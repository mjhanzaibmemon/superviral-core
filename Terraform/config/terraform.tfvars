environment = "dev"
aws_region  = "us-east-1"

ecs_task_cpu    = 256
ecs_task_memory = 512
ecs_desired_count = 1

ecr_repository_name = "my-ecr-repo"

alb_name = "my-app-alb-dev"
alb_listener_port = 80

rds_identifier      = "my-mysql-dev-db"
rds_instance_class  = "db.t3.micro"
rds_engine_version  = "8.0"
rds_allocated_storage = 20
ecs_container_image = ""

tags = {
  Environment = "dev"
  Project     = "superviral"
  ManagedBy   = "terraform"
}
