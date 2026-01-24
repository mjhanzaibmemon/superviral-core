################################################################################
#                         Data Sources - Secrets Manager                       #
################################################################################
# Fetch initial DB credentials from Secrets Manager
data "aws_secretsmanager_secret_version" "db_credentials" {
  count     = var.db_secrets_arn != "" ? 1 : 0
  secret_id = var.db_secrets_arn
}

locals {
  # Parse the initial secret JSON
  # User must create secret with: DB_USER, DB_PASS, DB_NAME, DB_PORT, REDIS_PORT
  initial_credentials = var.db_secrets_arn != "" ? jsondecode(data.aws_secretsmanager_secret_version.db_credentials[0].secret_string) : {}

  db_user   = lookup(local.initial_credentials, "DB_USER", "admin")
  db_pass   = lookup(local.initial_credentials, "DB_PASS", "changeme")
  db_name   = lookup(local.initial_credentials, "DB_NAME", var.rds_db_name)
  db_port   = lookup(local.initial_credentials, "DB_PORT", "3306")
  redis_port = lookup(local.initial_credentials, "REDIS_PORT", "6379")
}

################################################################################
#                  Update Secrets Manager with Host Values                     #
################################################################################
# After RDS/Redis created, update secret with actual HOST values
resource "aws_secretsmanager_secret_version" "db_credentials_updated" {
  count     = var.db_secrets_arn != "" ? 1 : 0
  secret_id = var.db_secrets_arn

  secret_string = jsonencode({
    DB_USER    = local.db_user
    DB_PASS    = local.db_pass
    DB_NAME    = local.db_name
    DB_PORT    = local.db_port
    DB_HOST    = module.rds_instance.db_endpoint_address
    REDIS_HOST = module.redis.primary_endpoint_address
    REDIS_PORT = local.redis_port
  })

  depends_on = [module.rds_instance, module.redis]
}

################################################################################
#                               VPC                                            #
################################################################################

module "vpc" {
  source     = "../modules/vpc"
  cidr_block = "10.0.0.0/16"
  tags_name  = "superviral-vpc"
}

################################################################################
#                         ECS Subnets                                          #
################################################################################

module "ecs_subnet_1" {
  source                  = "../modules/subnet"
  vpc_id                  = module.vpc.vpc_id
  cidr_block              = "10.0.2.0/24"
  map_public_ip_on_launch = true
  availability_zone       = "${var.aws_region}a"

  depends_on = [module.vpc]
}

module "ecs_subnet_2" {
  source                  = "../modules/subnet"
  vpc_id                  = module.vpc.vpc_id
  cidr_block              = "10.0.1.0/24"
  map_public_ip_on_launch = true
  availability_zone       = "${var.aws_region}b"

  depends_on = [module.vpc]
}

################################################################################
#                         RDS Subnet                                           #
################################################################################
module "rds_subnet_1" {
  source                  = "../modules/subnet"
  vpc_id                  = module.vpc.vpc_id
  cidr_block              = "10.0.3.0/24"
  map_public_ip_on_launch = false
  availability_zone       = "${var.aws_region}b"

  depends_on = [module.vpc]
}

module "rds_subnet_2" {
  source                  = "../modules/subnet"
  vpc_id                  = module.vpc.vpc_id
  cidr_block              = "10.0.4.0/24"
  map_public_ip_on_launch = false
  availability_zone       = "${var.aws_region}a"

  depends_on = [module.vpc]
}

################################################################################
#                         Lambda Subnet                                        #
################################################################################
module "lambda_subnet_2" {
  source                  = "../modules/subnet"
  vpc_id                  = module.vpc.vpc_id
  cidr_block              = "10.0.5.0/24"
  map_public_ip_on_launch = false
  availability_zone       = "${var.aws_region}a"

  depends_on = [module.vpc]
}

################################################################################
#                   ECS TASK EXECUTION ROLE                                    #
################################################################################
module "ecs_task_execution_role" {
  source    = "../modules/iam"
  role_name = "superviral-task-exec-role-${var.environment}"
  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [{
      Effect    = "Allow"
      Principal = { Service = "ecs-tasks.amazonaws.com" }
      Action    = "sts:AssumeRole"
    }]
  })
}

module "ecs_task_role" {
  source    = "../modules/iam"
  role_name = "superviral-task-role-${var.environment}"
  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [{
      Effect    = "Allow"
      Principal = { Service = "ecs-tasks.amazonaws.com" }
      Action    = "sts:AssumeRole"
    }]
  })
}

################################################################################
#                   ECS Task Execution Role Policy Attachments                 #
################################################################################
module "ecs_task_execution_policy_attachment" {
  source                      = "../modules/iam_role_policy_attachment"
  policy_attachment_role_name = module.ecs_task_execution_role.role_name
  cluster_policy_arn          = "arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy"

  depends_on = [module.ecs_task_execution_role]
}

module "ecs_task_execution_ecr_policy_attachment" {
  source                      = "../modules/iam_role_policy_attachment"
  policy_attachment_role_name = module.ecs_task_execution_role.role_name
  cluster_policy_arn          = "arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryReadOnly"

  depends_on = [module.ecs_task_execution_role]
}

# Secrets Manager read permission for ECS
resource "aws_iam_role_policy" "ecs_secrets_manager_policy" {
  name = "superviral-secrets-manager-policy-${var.environment}"
  role = module.ecs_task_execution_role.role_name

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Effect = "Allow"
        Action = [
          "secretsmanager:GetSecretValue"
        ]
        Resource = var.db_secrets_arn != "" ? [var.db_secrets_arn] : ["*"]
      }
    ]
  })

  depends_on = [module.ecs_task_execution_role]
}

################################################################################
#                   ECS Task Role Policy Attachments                           #
################################################################################
module "ecs_task_ecr_policy_attachment" {
  source                      = "../modules/iam_role_policy_attachment"
  policy_attachment_role_name = module.ecs_task_role.role_name
  cluster_policy_arn          = "arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryReadOnly"

  depends_on = [module.ecs_task_role]
}

################################################################################
#                         ECS Cluster                                          #
################################################################################
module "ecs_cluster" {
  source       = "../modules/ecs_cluster"
  cluster_name = "superviral-cluster-${var.environment}"

  enable_container_insights      = true
  default_capacity_provider_base = 1

  depends_on = [module.vpc]
}

################################################################################
#                      CloudWatch Log Group                                    #
################################################################################
module "cloudwatch_log_group" {
  source            = "../modules/cloudwatch_log_group"
  log_group_name    = "/ecs/superviral-${var.environment}"
  retention_in_days = 7
}

################################################################################
#                    ECS Task Definition                                       #
################################################################################
module "ecs_task_definition" {
  source = "../modules/ecs_task_definition"

  task_family        = "superviral-${var.environment}"
  container_name     = "superviral-container"
  container_image    = var.ecs_container_image != "" ? var.ecs_container_image : module.ecr.ecr_repository_url
  container_port     = 80
  task_cpu           = var.ecs_task_cpu
  task_memory        = var.ecs_task_memory
  execution_role_arn = module.ecs_task_execution_role.role_arn
  task_role_arn      = module.ecs_task_role.role_arn
  log_group_name     = module.cloudwatch_log_group.log_group_name
  aws_region         = var.aws_region

  # Only ENVIRONMENT is hardcoded - everything else from Secrets Manager
  container_environment = [
    {
      name  = "ENVIRONMENT"
      value = var.environment
    }
  ]

  # ALL DB/Redis values from Secrets Manager
  container_secrets = var.db_secrets_arn != "" ? [
    {
      name      = "DB_HOST"
      valueFrom = "${var.db_secrets_arn}:DB_HOST::"
    },
    {
      name      = "DB_USER"
      valueFrom = "${var.db_secrets_arn}:DB_USER::"
    },
    {
      name      = "DB_PASS"
      valueFrom = "${var.db_secrets_arn}:DB_PASS::"
    },
    {
      name      = "DB_NAME"
      valueFrom = "${var.db_secrets_arn}:DB_NAME::"
    },
    {
      name      = "DB_PORT"
      valueFrom = "${var.db_secrets_arn}:DB_PORT::"
    },
    {
      name      = "REDIS_HOST"
      valueFrom = "${var.db_secrets_arn}:REDIS_HOST::"
    },
    {
      name      = "REDIS_PORT"
      valueFrom = "${var.db_secrets_arn}:REDIS_PORT::"
    }
  ] : []

  depends_on = [
    module.ecs_task_execution_role,
    module.ecs_task_role,
    module.cloudwatch_log_group,
    module.ecr,
    aws_secretsmanager_secret_version.db_credentials_updated
  ]
}

################################################################################
#                    Application Load Balancer                                 #
################################################################################
module "alb" {
  source = "../modules/alb"

  alb_name                   = "superviral-alb-${var.environment}"
  alb_security_group_name    = "superviral-alb-sg-${var.environment}"
  vpc_id                     = module.vpc.vpc_id
  subnet_ids                 = [module.ecs_subnet_1.subnet_id, module.ecs_subnet_2.subnet_id]
  internal                   = false
  enable_deletion_protection = false

  depends_on = [module.vpc, module.ecs_subnet_1, module.ecs_subnet_2]
}

################################################################################
#                      ALB Target Group                                        #
################################################################################
module "alb_target_group" {
  source = "../modules/alb_target_group"

  target_group_name = "superviral-tg-${var.environment}"
  target_port       = 80

  vpc_id = module.vpc.vpc_id
  health_check_healthy_threshold   = 2
  health_check_unhealthy_threshold = 2
  health_check_timeout            = 5
  health_check_interval           = 30
  health_check_path               = "/health.php"
  health_check_matcher            = "200-299"

  depends_on = [module.vpc]
}

################################################################################
#                       ALB Listener                                           #
################################################################################
module "alb_listener" {
  source = "../modules/alb_listener"

  alb_arn          = module.alb.alb_arn
  listener_port    = 80
  listener_protocol = "HTTP"
  target_group_arn = module.alb_target_group.target_group_arn

  depends_on = [module.alb, module.alb_target_group]
}

################################################################################
#                      ECS Service                                             #
################################################################################
module "ecs_service" {
  source = "../modules/ecs_service"

  service_name          = "superviral-service-${var.environment}"
  cluster_id            = module.ecs_cluster.cluster_id
  task_definition_arn   = module.ecs_task_definition.task_definition_arn
  desired_count         = var.ecs_desired_count
  vpc_id                = module.vpc.vpc_id
  subnet_ids            = [module.ecs_subnet_1.subnet_id, module.ecs_subnet_2.subnet_id]
  security_group_name   = "superviral-ecs-sg-${var.environment}"
  container_name        = "superviral-container"
  container_port        = 80
  target_group_arn      = module.alb_target_group.target_group_arn
  alb_security_group_id = module.alb.alb_security_group_id
  assign_public_ip      = true

  depends_on = [
    module.ecs_cluster,
    module.ecs_task_definition,
    module.alb_target_group,
    module.alb
  ]
}

################################################################################
#                           ECR Repository                                     #
################################################################################
module "ecr" {
  source               = "../modules/ecr"
  ecr_repository_name  = "superviral-ecr"
  image_tag_mutability = "MUTABLE"
  scan_on_push         = true

  depends_on = [module.vpc]
}

################################################################################
#                           Internet Gateway                                   #
################################################################################
module "internet_gateway" {
  source = "../modules/igw"
  vpc_id = module.vpc.vpc_id

  depends_on = [module.vpc]
}

################################################################################
#                     RouteTable & Routes                                      #
################################################################################
module "route_table" {
  source = "../modules/route_table"

  vpc_id  = module.vpc.vpc_id
  rt_name = "superviral-route-${var.environment}"

  depends_on = [module.ecs_subnet_1, module.ecs_subnet_2]
}

module "routes" {
  source = "../modules/route"

  route_table_id         = module.route_table.id
  destination_cidr_block = "0.0.0.0/0"
  gateway_id             = module.internet_gateway.igw_id

  depends_on = [module.route_table]
}

################################################################################
#                    Route Table Associations                                  #
################################################################################
module "subnet_1_rt_association" {
  source    = "../modules/route_table_association"
  subnet_id = module.ecs_subnet_1.subnet_id
  route_table_id = module.route_table.id

  depends_on = [module.route_table]
}

module "subnet_2_rt_association" {
  source = "../modules/route_table_association"
  subnet_id      = module.ecs_subnet_2.subnet_id
  route_table_id = module.route_table.id

  depends_on = [module.route_table]
}

################################################################################
#                           RDS Subnet Group                                   #
################################################################################
module "rds_subnet_group" {
  source = "../modules/rds_subnet_group"
  name   = "superviral-rds-subnet-group"
  subnet_ids = [
    module.rds_subnet_1.subnet_id,
    module.rds_subnet_2.subnet_id
  ]
  depends_on = [module.rds_subnet_1, module.rds_subnet_2]
}

################################################################################
#                           RDS Security Group                                 #
################################################################################
module "rds_sg" {
  source        = "../modules/rds_sg"
  name          = "rds-mysql-sg"
  vpc_id        = module.vpc.vpc_id
  db_port       = 3306
  ingress_cidrs = ["10.0.0.0/16"]

  depends_on = [module.vpc]
}

################################################################################
#                           RDS Parameter Group                               #
################################################################################
module "rds_parameter_group" {
  source = "../modules/rds_parameter_group"
  name   = "mysql-parameters"
  family = "mysql8.0"

  parameters = {
    max_connections = "200"
  }
}

################################################################################
#                           RDS Instance                                      #
################################################################################
# RDS - Credentials from Secrets Manager
module "rds_instance" {
  source = "../modules/rds_instance"

  identifier     = "superviral-${var.environment}-db"
  engine_version = var.rds_engine_version
  instance_class = var.rds_instance_class

  # Credentials from Secrets Manager
  username = local.db_user
  password = local.db_pass
  db_name  = local.db_name

  allocated_storage     = var.rds_allocated_storage
  max_allocated_storage = var.rds_max_allocated_storage

  storage_type = "gp3"

  db_subnet_group_name = module.rds_subnet_group.subnet_group_name
  sg_id                = module.rds_sg.sg_id

  depends_on = [
    module.rds_sg,
    module.rds_subnet_group
  ]
}

################################################################################
#                           Redis / ElastiCache                                #
################################################################################
# Redis - No authentication (internal VPC only)
module "redis" {
  source = "../modules/redis"

  vpc_id       = module.vpc.vpc_id
  subnet_ids   = [module.rds_subnet_1.subnet_id, module.rds_subnet_2.subnet_id]
  ingress_cidrs = ["10.0.0.0/16"]

  engine_version = "6.x"
  node_type      = "cache.t3.small"
  number_cache_clusters = 1

  depends_on = [module.vpc, module.rds_subnet_1, module.rds_subnet_2]
}
