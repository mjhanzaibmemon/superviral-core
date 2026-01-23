#!/bin/bash
set -e

echo "========================================"
echo "=== CONTAINER STARTUP - $(date) ==="
echo "========================================"

echo ""
echo "=== 1. ENVIRONMENT VARIABLES ==="
echo "DB_HOST: ${DB_HOST:-NOT SET}"
echo "DB_NAME: ${DB_NAME:-NOT SET}"
echo "DB_PORT: ${DB_PORT:-NOT SET}"
echo "DB_USER: ${DB_USER:-NOT SET}"
echo "DB_PASS: ${DB_PASS:+SET (hidden)}"
echo "REDIS_HOST: ${REDIS_HOST:-NOT SET}"
echo "REDIS_PORT: ${REDIS_PORT:-NOT SET}"
echo "ENVIRONMENT: ${ENVIRONMENT:-NOT SET}"

echo ""
echo "=== 2. FILE STRUCTURE CHECK ==="
echo "DocumentRoot contents:"
ls -la /var/www/html/superviral.io/ 2>/dev/null | head -20 || echo "ERROR: superviral.io directory not found!"

echo ""
echo "=== 3. KEY FILES CHECK ==="
for file in index.php db.php header.php health.php; do
    if [ -f "/var/www/html/superviral.io/$file" ]; then
        size=$(stat -c%s "/var/www/html/superviral.io/$file" 2>/dev/null || echo "unknown")
        echo "[OK] $file exists ($size bytes)"
    else
        echo "[ERROR] $file MISSING!"
    fi
done

echo ""
echo "=== 4. ETRA.GROUP CHECK (loadParamStore.php) ==="
if [ -f "/var/www/html/etra.group/loadParamStore.php" ]; then
    echo "[OK] loadParamStore.php exists"
else
    echo "[ERROR] loadParamStore.php MISSING!"
fi

echo ""
echo "=== 5. DATABASE CONNECTION TEST ==="
php -r "
try {
    \$host = getenv('DB_HOST') ?: 'not-set';
    \$db = getenv('DB_NAME') ?: 'not-set';
    \$user = getenv('DB_USER') ?: 'not-set';
    \$pass = getenv('DB_PASS') ?: '';
    \$port = getenv('DB_PORT') ?: '3306';

    echo \"Connecting to \$host:\$port/\$db as \$user...\n\";
    \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo \"[OK] Database connected successfully!\n\";

    // Test content table
    \$stmt = \$pdo->query('SELECT COUNT(*) as cnt FROM content');
    \$row = \$stmt->fetch();
    echo \"[OK] Content table has \" . \$row['cnt'] . \" rows\n\";
} catch (Exception \$e) {
    echo \"[ERROR] Database: \" . \$e->getMessage() . \"\n\";
}
" 2>&1 || echo "[ERROR] PHP database test failed"

echo ""
echo "=== 6. REDIS CONNECTION TEST ==="
REDIS_HOST_VAL="${REDIS_HOST:-localhost}"
REDIS_PORT_VAL="${REDIS_PORT:-6379}"
echo "Testing Redis at $REDIS_HOST_VAL:$REDIS_PORT_VAL..."
if redis-cli -h "$REDIS_HOST_VAL" -p "$REDIS_PORT_VAL" ping 2>/dev/null | grep -q PONG; then
    echo "[OK] Redis connected successfully!"
else
    echo "[WARNING] Redis connection failed (may be optional)"
fi

echo ""
echo "=== 7. PHP CONFIGURATION ==="
php -i 2>/dev/null | grep -E "(error_log|display_errors|memory_limit)" | head -5 || echo "Could not get PHP info"

echo ""
echo "=== 8. APACHE CONFIGURATION TEST ==="
apache2ctl -t 2>&1 || echo "[WARNING] Apache config test issue"

echo ""
echo "========================================"
echo "=== STARTING APACHE SERVER ==="
echo "========================================"

# Start Apache in foreground
exec apache2-foreground
