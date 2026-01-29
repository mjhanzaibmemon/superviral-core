resource "aws_security_group" "ecs_tasks" {
  name        = var.security_group_name
  description = "Security group for ECS tasks"
  vpc_id      = var.vpc_id

  # Revoke rules before resource destruction
  revoke_rules_on_delete = true

  ingress {
    from_port       = var.container_port
    to_port         = var.container_port
    protocol        = "tcp"
    security_groups = [var.alb_security_group_id]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = var.security_group_name
  }
}

resource "aws_ecs_service" "this" {
  name            = var.service_name
  cluster         = var.cluster_id
  task_definition = var.task_definition_arn
  desired_count   = var.desired_count
  launch_type     = "FARGATE"

  # Zero-downtime deployment configuration
  # Minimum 100% ensures old tasks keep running until new ones are healthy
  deployment_minimum_healthy_percent = 100
  deployment_maximum_percent         = 200
  
  # Health check grace period - reduced to 0 for fastest deployment
  # Tasks start receiving traffic as soon as they pass first health check
  health_check_grace_period_seconds = 0

  # Deployment circuit breaker - disabled for faster deployments
  # Warning: Bad deployments won't auto-rollback
  deployment_circuit_breaker {
    enable   = false
    rollback = false
  }

  network_configuration {
    subnets          = var.subnet_ids
    security_groups  = [aws_security_group.ecs_tasks.id]
    assign_public_ip = var.assign_public_ip
  }

  load_balancer {
    target_group_arn = var.target_group_arn
    container_name   = var.container_name
    container_port   = var.container_port
  }

  # Timeout for graceful task drainage during destruction
  timeouts {
    delete = "10m"
  }

  depends_on = [
    aws_security_group.ecs_tasks
  ]

  tags = {
    Name = var.service_name
  }
}
