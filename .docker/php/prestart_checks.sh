#!/usr/bin/env bash
# Pre-start checks for external dependencies (Redis, DB, HTTP endpoints)
# Logs to both /var/log/prestart_checks.log AND stdout (for CloudWatch)
# Includes retry logic for services that may not be ready immediately

set -o pipefail

LOGFILE="/var/log/prestart_checks.log"
MAX_RETRIES="${PRESTART_MAX_RETRIES:-5}"
RETRY_DELAY="${PRESTART_RETRY_DELAY:-5}"

# Log to both file and stdout (CloudWatch captures stdout)
log() {
  local level="$1"
  local msg="$2"
  local ts
  ts=$(date -u +"%Y-%m-%dT%H:%M:%SZ")
  echo "[$ts] [$level] $msg" | tee -a "$LOGFILE"
}

PASS=0
FAIL=0
WARNINGS=0

# Retry wrapper function
retry_check() {
  local check_name="$1"
  local check_func="$2"
  local attempt=1

  while [ $attempt -le $MAX_RETRIES ]; do
    log "INFO" "[$check_name] Attempt $attempt/$MAX_RETRIES"

    if $check_func; then
      return 0
    fi

    if [ $attempt -lt $MAX_RETRIES ]; then
      log "WARN" "[$check_name] Failed, retrying in ${RETRY_DELAY}s..."
      sleep $RETRY_DELAY
    fi

    attempt=$((attempt+1))
  done

  log "ERROR" "[$check_name] All $MAX_RETRIES attempts failed"
  return 1
}

check_redis_impl() {
  RH="${REDIS_HOST:-127.0.0.1}"
  RP="${REDIS_PORT:-6379}"

  log "DEBUG" "Redis check: host=$RH port=$RP"

  # First check TCP connectivity
  if ! nc -z -w5 "$RH" "$RP" 2>/dev/null; then
    log "ERROR" "Redis TCP port $RH:$RP not reachable (nc failed)"
    log "DEBUG" "Attempting DNS resolution for $RH..."
    if command -v getent >/dev/null 2>&1; then
      getent hosts "$RH" 2>&1 | while read line; do log "DEBUG" "DNS: $line"; done
    elif command -v nslookup >/dev/null 2>&1; then
      nslookup "$RH" 2>&1 | head -10 | while read line; do log "DEBUG" "DNS: $line"; done
    fi
    return 1
  fi

  log "DEBUG" "Redis TCP port open, checking PING..."

  if command -v php >/dev/null 2>&1; then
    # Capture PHP output for debugging
    local php_output
    php_output=$(php -r "
      error_reporting(E_ALL);
      ini_set('display_errors', 1);
      try {
        \$r = new Redis();
        \$host = getenv('REDIS_HOST') ?: '$RH';
        \$port = intval(getenv('REDIS_PORT') ?: $RP);
        echo \"Connecting to \$host:\$port\\n\";
        if (!\$r->connect(\$host, \$port, 5)) {
          echo \"Connection failed\\n\";
          exit(2);
        }
        \$pong = \$r->ping();
        echo \"PING response: \$pong\\n\";
        if (\$pong === true || \$pong === '+PONG' || \$pong === 'PONG') {
          echo \"SUCCESS\\n\";
          exit(0);
        }
        echo \"Unexpected PING response\\n\";
        exit(3);
      } catch (Exception \$e) {
        echo \"Exception: \" . \$e->getMessage() . \"\\n\";
        exit(4);
      }
    " 2>&1)
    rc=$?

    log "DEBUG" "PHP Redis check output: $php_output"

    if [ $rc -eq 0 ]; then
      log "OK" "Redis reachable and responding to PING"
      PASS=$((PASS+1))
      return 0
    else
      log "ERROR" "Redis PING failed (exit code: $rc)"
      return 1
    fi
  elif command -v redis-cli >/dev/null 2>&1; then
    local cli_output
    cli_output=$(redis-cli -h "$RH" -p "$RP" PING 2>&1)
    if echo "$cli_output" | grep -q PONG; then
      log "OK" "Redis reachable (redis-cli PONG)"
      PASS=$((PASS+1))
      return 0
    else
      log "ERROR" "Redis PING failed (redis-cli): $cli_output"
      return 1
    fi
  else
    log "WARN" "No Redis client available, TCP port is open - assuming OK"
    PASS=$((PASS+1))
    WARNINGS=$((WARNINGS+1))
    return 0
  fi
}

check_mysql_impl() {
  MH="${DB_HOST:-127.0.0.1}"
  MP="${DB_PORT:-3306}"
  MU="${DB_USER:-root}"
  MPASS="${DB_PASS:-}"
  MDB="${DB_NAME:-mysql}"

  log "DEBUG" "MySQL check: host=$MH port=$MP user=$MU db=$MDB"

  # First check TCP connectivity
  if ! nc -z -w5 "$MH" "$MP" 2>/dev/null; then
    log "ERROR" "MySQL TCP port $MH:$MP not reachable (nc failed)"
    log "DEBUG" "Attempting DNS resolution for $MH..."
    if command -v getent >/dev/null 2>&1; then
      getent hosts "$MH" 2>&1 | while read line; do log "DEBUG" "DNS: $line"; done
    elif command -v nslookup >/dev/null 2>&1; then
      nslookup "$MH" 2>&1 | head -10 | while read line; do log "DEBUG" "DNS: $line"; done
    fi
    return 1
  fi

  log "DEBUG" "MySQL TCP port open, checking authentication..."

  if command -v php >/dev/null 2>&1; then
    local php_output
    php_output=$(php -r "
      error_reporting(E_ALL);
      ini_set('display_errors', 1);
      \$host = getenv('DB_HOST') ?: '$MH';
      \$user = getenv('DB_USER') ?: '$MU';
      \$pass = getenv('DB_PASS') ?: '$MPASS';
      \$db   = getenv('DB_NAME') ?: '$MDB';
      \$port = intval(getenv('DB_PORT') ?: $MP);

      echo \"Connecting to \$host:\$port as \$user to database \$db\\n\";

      mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
      try {
        \$m = new mysqli(\$host, \$user, \$pass, \$db, \$port);
        if (\$m->connect_error) {
          echo \"Connection error: \" . \$m->connect_error . \"\\n\";
          exit(2);
        }
        echo \"Connected successfully\\n\";
        echo \"Server info: \" . \$m->server_info . \"\\n\";
        \$m->close();
        exit(0);
      } catch (mysqli_sql_exception \$e) {
        echo \"MySQL Exception: \" . \$e->getMessage() . \"\\n\";
        exit(3);
      } catch (Exception \$e) {
        echo \"General Exception: \" . \$e->getMessage() . \"\\n\";
        exit(4);
      }
    " 2>&1)
    rc=$?

    log "DEBUG" "PHP MySQL check output: $php_output"

    if [ $rc -eq 0 ]; then
      log "OK" "MySQL reachable and authentication succeeded"
      PASS=$((PASS+1))
      return 0
    else
      log "ERROR" "MySQL connection failed (exit code: $rc)"
      return 1
    fi
  else
    log "WARN" "No MySQL client (PHP) available, TCP port is open - assuming OK"
    PASS=$((PASS+1))
    WARNINGS=$((WARNINGS+1))
    return 0
  fi
}

check_http() {
  URL="$1"
  log "DEBUG" "HTTP check: $URL"

  if command -v curl >/dev/null 2>&1; then
    local http_output
    local http_code
    http_output=$(curl -fsS --max-time 10 -w "\nHTTP_CODE:%{http_code}" "$URL" 2>&1)
    rc=$?
    http_code=$(echo "$http_output" | grep "HTTP_CODE:" | cut -d: -f2)

    if [ $rc -eq 0 ]; then
      log "OK" "HTTP $URL returned success (code: $http_code)"
      PASS=$((PASS+1))
      return 0
    else
      log "ERROR" "HTTP $URL failed (curl exit: $rc, code: $http_code)"
      return 1
    fi
  else
    log "WARN" "curl not available; skipping HTTP check for $URL"
    PASS=$((PASS+1))
    WARNINGS=$((WARNINGS+1))
    return 0
  fi
}

# ============================================================================
# MAIN
# ============================================================================

log "INFO" "=========================================="
log "INFO" "Starting prestart checks"
log "INFO" "=========================================="
log "INFO" "Configuration:"
log "INFO" "  REDIS_HOST=${REDIS_HOST:-<unset>}"
log "INFO" "  REDIS_PORT=${REDIS_PORT:-<unset>}"
log "INFO" "  DB_HOST=${DB_HOST:-<unset>}"
log "INFO" "  DB_PORT=${DB_PORT:-<unset>}"
log "INFO" "  DB_USER=${DB_USER:-<unset>}"
log "INFO" "  DB_NAME=${DB_NAME:-<unset>}"
log "INFO" "  MAX_RETRIES=$MAX_RETRIES"
log "INFO" "  RETRY_DELAY=${RETRY_DELAY}s"
log "INFO" "=========================================="

# Check if required env vars are set
MISSING_VARS=0
if [ -z "$REDIS_HOST" ]; then
  log "ERROR" "REDIS_HOST environment variable is not set!"
  MISSING_VARS=1
fi
if [ -z "$DB_HOST" ]; then
  log "ERROR" "DB_HOST environment variable is not set!"
  MISSING_VARS=1
fi

if [ $MISSING_VARS -eq 1 ]; then
  log "ERROR" "Missing required environment variables. Check ECS task definition."
  log "INFO" "Available environment variables:"
  env | grep -E "^(REDIS_|DB_|AWS_|ECS_)" | while read line; do log "DEBUG" "  $line"; done
fi

# Run checks with retry
log "INFO" "------------------------------------------"
log "INFO" "Checking Redis..."
if retry_check "Redis" check_redis_impl; then
  log "INFO" "Redis check PASSED"
else
  FAIL=$((FAIL+1))
  log "ERROR" "Redis check FAILED after $MAX_RETRIES attempts"
fi

log "INFO" "------------------------------------------"
log "INFO" "Checking MySQL..."
if retry_check "MySQL" check_mysql_impl; then
  log "INFO" "MySQL check PASSED"
else
  FAIL=$((FAIL+1))
  log "ERROR" "MySQL check FAILED after $MAX_RETRIES attempts"
fi

# Optional HTTP checks
if [ -n "$CHECK_URLS" ]; then
  log "INFO" "------------------------------------------"
  log "INFO" "Checking HTTP endpoints..."
  IFS=',' read -ra URLS <<< "$CHECK_URLS"
  for u in "${URLS[@]}"; do
    if ! retry_check "HTTP:$u" "check_http $u"; then
      FAIL=$((FAIL+1))
    fi
  done
fi

# Summary
log "INFO" "=========================================="
log "INFO" "Prestart checks summary:"
log "INFO" "  PASSED:   $PASS"
log "INFO" "  FAILED:   $FAIL"
log "INFO" "  WARNINGS: $WARNINGS"
log "INFO" "=========================================="

if [ $FAIL -gt 0 ]; then
  log "ERROR" "One or more prestart checks failed; aborting startup"
  log "ERROR" "Container will NOT start Apache until dependencies are reachable"
  exit 1
else
  log "INFO" "All prestart checks passed - starting Apache"
  exit 0
fi
