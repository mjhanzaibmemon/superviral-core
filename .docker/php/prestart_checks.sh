#!/usr/bin/env bash
# Pre-start checks for external dependencies (Redis, DB, HTTP endpoints)
# Logs to /var/log/prestart_checks.log and exits non-zero on failures to prevent Apache start.

LOGFILE="/var/log/prestart_checks.log"
TS() { date -u +"%Y-%m-%dT%H:%M:%SZ"; }
echo "$(TS) [INFO] Starting prestart checks" >> "$LOGFILE"

PASS=0
FAIL=0

check_redis() {
  RH="${REDIS_HOST:-127.0.0.1}"
  RP="${REDIS_PORT:-6379}"
  echo "$(TS) [CHECK] Redis -> $RH:$RP" >> "$LOGFILE"

  if command -v php >/dev/null 2>&1; then
    php -r "\$r=new Redis();@\$r->connect(getenv('REDIS_HOST')?:'$RH', getenv('REDIS_PORT')?:$RP, 2) or exit(2); if(\$r->ping()==='PONG'){ exit(0);} exit(3);" 
    rc=$?
    if [ $rc -eq 0 ]; then
      echo "$(TS) [OK] Redis reachable and responding" >> "$LOGFILE"; PASS=$((PASS+1)); return 0
    else
      echo "$(TS) [FAIL] Redis ping failed (php redis extension) rc=$rc" >> "$LOGFILE"; FAIL=$((FAIL+1)); return 1
    fi
  elif command -v redis-cli >/dev/null 2>&1; then
    if redis-cli -h "$RH" -p "$RP" PING 2>/dev/null | grep -q PONG; then
      echo "$(TS) [OK] Redis reachable (redis-cli)" >> "$LOGFILE"; PASS=$((PASS+1)); return 0
    else
      echo "$(TS) [FAIL] Redis ping failed (redis-cli)" >> "$LOGFILE"; FAIL=$((FAIL+1)); return 1
    fi
  else
    # fallback: check TCP port
    if nc -z -w3 "$RH" "$RP" >/dev/null 2>&1; then
      echo "$(TS) [WARN] Redis TCP port open but no higher-level check available" >> "$LOGFILE"; PASS=$((PASS+1)); return 0
    else
      echo "$(TS) [FAIL] Redis TCP port closed" >> "$LOGFILE"; FAIL=$((FAIL+1)); return 1
    fi
  fi
}

check_mysql() {
  MH="${DB_HOST:-127.0.0.1}"
  MP="${DB_PORT:-3306}"
  MU="${DB_USER:-root}"
  MPASS="${DB_PASS:-}" 
  MDB="${DB_NAME:-mysql}"
  echo "$(TS) [CHECK] MySQL -> $MH:$MP (user=$MU db=$MDB)" >> "$LOGFILE"

  if command -v php >/dev/null 2>&1; then
    php -r "@\$m = @new mysqli(getenv('DB_HOST')?:'$MH', getenv('DB_USER')?:'$MU', getenv('DB_PASS')?:'$MPASS', getenv('DB_NAME')?:'$MDB', intval(getenv('DB_PORT')?:$MP)); if(\$m && !\$m->connect_error){ exit(0);} fwrite(STDERR, \$m->connect_error); exit(2);"
    rc=$?
    if [ $rc -eq 0 ]; then
      echo "$(TS) [OK] MySQL reachable and authentication succeeded" >> "$LOGFILE"; PASS=$((PASS+1)); return 0
    else
      echo "$(TS) [FAIL] MySQL connection failed (php mysqli) rc=$rc" >> "$LOGFILE"; FAIL=$((FAIL+1)); return 1
    fi
  else
    if nc -z -w3 "$MH" "$MP" >/dev/null 2>&1; then
      echo "$(TS) [WARN] MySQL TCP port open but no client available for auth check" >> "$LOGFILE"; PASS=$((PASS+1)); return 0
    else
      echo "$(TS) [FAIL] MySQL TCP port closed" >> "$LOGFILE"; FAIL=$((FAIL+1)); return 1
    fi
  fi
}

check_http() {
  URL="$1"
  echo "$(TS) [CHECK] HTTP -> $URL" >> "$LOGFILE"
  if command -v curl >/dev/null 2>&1; then
    if curl -fsS --max-time 5 "$URL" >/dev/null 2>&1; then
      echo "$(TS) [OK] HTTP $URL returned success" >> "$LOGFILE"; PASS=$((PASS+1)); return 0
    else
      echo "$(TS) [FAIL] HTTP $URL failed" >> "$LOGFILE"; FAIL=$((FAIL+1)); return 1
    fi
  else
    echo "$(TS) [WARN] curl not available; skipping HTTP check for $URL" >> "$LOGFILE"; PASS=$((PASS+1)); return 0
  fi
}

# Run checks
echo "$(TS) [INFO] Environment: REDIS_HOST=${REDIS_HOST:-unset} REDIS_PORT=${REDIS_PORT:-unset} DB_HOST=${DB_HOST:-unset} DB_PORT=${DB_PORT:-unset}" >> "$LOGFILE"

check_redis
check_mysql

if [ -n "$CHECK_URLS" ]; then
  IFS=',' read -ra URLS <<< "$CHECK_URLS"
  for u in "${URLS[@]}"; do
    check_http "$u"
  done
fi

echo "$(TS) [INFO] Prestart summary: PASS=$PASS FAIL=$FAIL" >> "$LOGFILE"
if [ $FAIL -gt 0 ]; then
  echo "$(TS) [ERROR] One or more prestart checks failed; aborting startup" >> "$LOGFILE"
  exit 1
else
  echo "$(TS) [INFO] All prestart checks passed" >> "$LOGFILE"
  exit 0
fi
