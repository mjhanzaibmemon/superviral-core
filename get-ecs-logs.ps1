# ECS Debug Logs Script
# Run: .\get-ecs-logs.ps1

$ErrorActionPreference = "Continue"
$outputFile = "ecs-debug-logs-$(Get-Date -Format 'yyyyMMdd-HHmmss').txt"

Write-Host "=== ECS Debug Logs ===" -ForegroundColor Cyan
Write-Host "Collecting logs... Output will be saved to: $outputFile" -ForegroundColor Yellow

# Start logging to file
Start-Transcript -Path $outputFile -Append

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "1. ECS CLUSTER INFO" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
aws ecs list-clusters --region eu-west-2

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "2. ECS SERVICES" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
aws ecs list-services --cluster my-app-cluster --region eu-west-2

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "3. SERVICE DETAILS & EVENTS (Last deployment info)" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
aws ecs describe-services --cluster my-app-cluster --services my-app-service --region eu-west-2

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "4. RUNNING TASKS" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
$tasks = aws ecs list-tasks --cluster my-app-cluster --region eu-west-2 --output json | ConvertFrom-Json
$tasks

if ($tasks.taskArns.Count -gt 0) {
    Write-Host "`n========================================" -ForegroundColor Green
    Write-Host "5. TASK DETAILS (Container status, health, etc)" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    aws ecs describe-tasks --cluster my-app-cluster --tasks $tasks.taskArns --region eu-west-2
}

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "6. CLOUDWATCH LOGS - Last 100 entries (All container logs)" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
$endTime = [int64](Get-Date -UFormat %s) * 1000
$startTime = $endTime - (3600000)  # Last 1 hour

Write-Host "Fetching logs from /ecs/my-app-task..." -ForegroundColor Yellow
aws logs get-log-events --log-group-name "/ecs/my-app-task" --log-stream-name "ecs/my-app-container/$(($tasks.taskArns[0] -split '/')[-1])" --limit 100 --region eu-west-2 2>$null

# If above fails, try to list all log streams and get from the latest one
Write-Host "`nListing all log streams..." -ForegroundColor Yellow
$logStreams = aws logs describe-log-streams --log-group-name "/ecs/my-app-task" --order-by LastEventTime --descending --limit 5 --region eu-west-2 --output json | ConvertFrom-Json

if ($logStreams.logStreams.Count -gt 0) {
    $latestStream = $logStreams.logStreams[0].logStreamName
    Write-Host "Latest log stream: $latestStream" -ForegroundColor Cyan

    Write-Host "`n========================================" -ForegroundColor Green
    Write-Host "7. LATEST LOG STREAM CONTENT" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    aws logs get-log-events --log-group-name "/ecs/my-app-task" --log-stream-name $latestStream --limit 200 --region eu-west-2
}

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "8. FILTER: PHP ERRORS ONLY" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
aws logs filter-log-events --log-group-name "/ecs/my-app-task" --filter-pattern "?error ?Error ?ERROR ?warning ?Warning ?WARNING ?fatal ?Fatal ?FATAL ?PHP" --limit 50 --region eu-west-2

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "9. FILTER: 500/503 HTTP ERRORS" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
aws logs filter-log-events --log-group-name "/ecs/my-app-task" --filter-pattern "?500 ?503 ?\"HTTP/1.1\" 5" --limit 50 --region eu-west-2

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "10. TARGET GROUP HEALTH" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
$tgArn = aws elbv2 describe-target-groups --names "my-app-tg" --region eu-west-2 --query "TargetGroups[0].TargetGroupArn" --output text 2>$null
if ($tgArn) {
    aws elbv2 describe-target-health --target-group-arn $tgArn --region eu-west-2
}

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "11. ECS EXEC INTO CONTAINER (Get PHP error log)" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Attempting to exec into container to get PHP error logs..." -ForegroundColor Yellow

if ($tasks.taskArns.Count -gt 0) {
    $taskId = ($tasks.taskArns[0] -split '/')[-1]
    Write-Host "Task ID: $taskId" -ForegroundColor Cyan

    # Try to get PHP error log from inside container
    Write-Host "`nGetting PHP error log..." -ForegroundColor Yellow
    aws ecs execute-command --cluster my-app-cluster --task $taskId --container my-app-container --interactive --command "cat /var/log/apache2/php_errors.log 2>/dev/null || echo 'No PHP error log found'" --region eu-west-2 2>$null

    Write-Host "`nGetting Apache error log..." -ForegroundColor Yellow
    aws ecs execute-command --cluster my-app-cluster --task $taskId --container my-app-container --interactive --command "tail -100 /var/log/apache2/error.log 2>/dev/null || echo 'No Apache error log found'" --region eu-west-2 2>$null

    Write-Host "`nChecking if index.php exists and is readable..." -ForegroundColor Yellow
    aws ecs execute-command --cluster my-app-cluster --task $taskId --container my-app-container --interactive --command "ls -la /var/www/html/superviral.io/index.php" --region eu-west-2 2>$null

    Write-Host "`nChecking db.php header..." -ForegroundColor Yellow
    aws ecs execute-command --cluster my-app-cluster --task $taskId --container my-app-container --interactive --command "head -50 /var/www/html/superviral.io/db.php" --region eu-west-2 2>$null

    Write-Host "`nTesting curl from inside container..." -ForegroundColor Yellow
    aws ecs execute-command --cluster my-app-cluster --task $taskId --container my-app-container --interactive --command "curl -v http://localhost/ 2>&1 | head -50" --region eu-west-2 2>$null
}

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "12. ECR IMAGE INFO (What image is deployed)" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
aws ecr describe-images --repository-name my-app-repo --region eu-west-2 --query 'sort_by(imageDetails,& imagePushedAt)[-1]'

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "13. TASK DEFINITION (Current config)" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
aws ecs describe-task-definition --task-definition my-app-task --region eu-west-2

Stop-Transcript

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "DONE! All logs saved to: $outputFile" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Share this file for debugging." -ForegroundColor Yellow
