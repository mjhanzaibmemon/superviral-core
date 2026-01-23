# ECS Container Debug Script - Container ke andar jake logs dekho
# Run: .\ecs-exec-debug.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "ECS Container Internal Debug" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Get running task ID
Write-Host "`nFinding running task..." -ForegroundColor Yellow
$tasks = aws ecs list-tasks --cluster my-app-cluster --region eu-west-2 --output json | ConvertFrom-Json

if ($tasks.taskArns.Count -eq 0) {
    Write-Host "ERROR: No running tasks found!" -ForegroundColor Red
    exit 1
}

$taskArn = $tasks.taskArns[0]
$taskId = ($taskArn -split '/')[-1]
Write-Host "Task ID: $taskId" -ForegroundColor Green

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "Choose what to check:" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "1. Apache Error Log (PHP errors yahan honge)"
Write-Host "2. Apache Access Log (requests)"
Write-Host "3. PHP Error Log"
Write-Host "4. Test curl localhost"
Write-Host "5. Check file permissions"
Write-Host "6. Check db.php content"
Write-Host "7. Check header.php content"
Write-Host "8. Check environment variables"
Write-Host "9. Interactive shell (manual debug)"
Write-Host "0. Run ALL checks"

$choice = Read-Host "`nEnter choice (0-9)"

function Run-EcsCommand {
    param($command)
    Write-Host "`nRunning: $command" -ForegroundColor Cyan
    aws ecs execute-command --cluster my-app-cluster --task $taskId --container my-app-container --interactive --command $command --region eu-west-2
}

switch ($choice) {
    "1" {
        Run-EcsCommand "tail -100 /var/log/apache2/error.log"
    }
    "2" {
        Run-EcsCommand "tail -100 /var/log/apache2/access.log"
    }
    "3" {
        Run-EcsCommand "cat /var/log/apache2/php_errors.log 2>/dev/null || echo 'PHP error log empty or not found'"
    }
    "4" {
        Run-EcsCommand "curl -v http://localhost/ 2>&1"
    }
    "5" {
        Run-EcsCommand "ls -la /var/www/html/superviral.io/"
    }
    "6" {
        Run-EcsCommand "head -50 /var/www/html/superviral.io/db.php"
    }
    "7" {
        Run-EcsCommand "head -30 /var/www/html/superviral.io/header.php"
    }
    "8" {
        Run-EcsCommand "env | grep -E '(DB_|REDIS_|AWS_|ENVIRONMENT)'"
    }
    "9" {
        Write-Host "Starting interactive shell... Type 'exit' to quit" -ForegroundColor Yellow
        aws ecs execute-command --cluster my-app-cluster --task $taskId --container my-app-container --interactive --command "/bin/bash" --region eu-west-2
    }
    "0" {
        Write-Host "`n=== RUNNING ALL CHECKS ===" -ForegroundColor Magenta

        Write-Host "`n--- Apache Error Log ---" -ForegroundColor Yellow
        Run-EcsCommand "tail -50 /var/log/apache2/error.log 2>/dev/null || echo 'No error log'"

        Write-Host "`n--- PHP Error Log ---" -ForegroundColor Yellow
        Run-EcsCommand "cat /var/log/apache2/php_errors.log 2>/dev/null || echo 'No PHP error log'"

        Write-Host "`n--- Curl Test ---" -ForegroundColor Yellow
        Run-EcsCommand "curl -s -o /dev/null -w 'HTTP Status: %{http_code}\n' http://localhost/"

        Write-Host "`n--- File Check ---" -ForegroundColor Yellow
        Run-EcsCommand "ls -la /var/www/html/superviral.io/index.php /var/www/html/superviral.io/db.php /var/www/html/superviral.io/header.php"

        Write-Host "`n--- Environment ---" -ForegroundColor Yellow
        Run-EcsCommand "env | grep -E '(DB_|REDIS_)'"

        Write-Host "`n--- DB Connection Test ---" -ForegroundColor Yellow
        Run-EcsCommand "php -r \"try { \\\$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASS')); echo 'DB Connected OK'; } catch(Exception \\\$e) { echo 'DB Error: ' . \\\$e->getMessage(); }\""
    }
    default {
        Write-Host "Invalid choice" -ForegroundColor Red
    }
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Done!" -ForegroundColor Cyan
