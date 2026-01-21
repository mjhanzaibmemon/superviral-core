################################################################################
#                               VPC                                            #
################################################################################

module "vpc" {
  source     = "../modules/vpc"
  cidr_block = "10.0.0.0/16"
  tags_name  = "vpc"
}

################################################################################
#                         EKS Cluster Subnets                                  #
################################################################################

module "eks_subnet_1" {
  source                  = "../modules/subnet"
  vpc_id                  = module.vpc.vpc_id
  cidr_block              = "10.0.2.0/24"
  map_public_ip_on_launch = true
  availability_zone       = "us-east-1a"

  depends_on = [module.vpc]
}

module "eks_subnet_2" {
  source                  = "../modules/subnet"
  vpc_id                  = module.vpc.vpc_id
  cidr_block              = "10.0.1.0/24"
  map_public_ip_on_launch = true
  availability_zone       = "us-east-1b"

  depends_on = [module.vpc]
}

################################################################################
#                         Rds Subnet                                 #
################################################################################
module "rds_subnet_1" {
  source                  = "../modules/subnet"
  vpc_id                  = module.vpc.vpc_id
  cidr_block              = "10.0.3.0/24"
  map_public_ip_on_launch = false
  availability_zone       = "us-east-1b"

  depends_on = [module.vpc]
}
module "rds_subnet_2" {
  source                  = "../modules/subnet"
  vpc_id                  = module.vpc.vpc_id
  cidr_block              = "10.0.4.0/24"
  map_public_ip_on_launch = false

  availability_zone = "us-east-1a"
  depends_on        = [module.vpc]
}
################################################################################
#                         lambda subnet                                    # 
module "lambda_subnet_2" {
  source                  = "../modules/subnet"
  vpc_id                  = module.vpc.vpc_id
  cidr_block              = "10.0.5.0/24"
  map_public_ip_on_launch = false

  availability_zone = "us-east-1a"
  depends_on        = [module.vpc]
}


################################################################################
#                           IAM ROLES                                          #
################################################################################

# [REMOVED - EKS-specific] EKS Cluster Role - No longer needed for ECS Fargate
# module "eks_cluster_iam_role" { ... }

# [REMOVED - EKS-specific] EKS Node Group Role - No longer needed for ECS Fargate
# module "eks_node_group_role" { ... }

################################################################################
#                   ECS TASK EXECUTION ROLE (DEV ONLY)                         #
################################################################################
# NEW: ECS Task Execution Role - Allows ECS to pull images and write logs
module "ecs_task_execution_role" {
  source    = "../modules/iam"
  role_name = "ecs_task_execution_role_dev"
  assume_role_policy = jsonencode({
    Version = "2012-10-17"
    Statement = [{
      Effect    = "Allow"
      Principal = { Service = "ecs-tasks.amazonaws.com" }
      Action    = "sts:AssumeRole"
    }]
  })
}

# NEW: ECS Task Role - Allows containerized application to access AWS services
module "ecs_task_role" {
  source    = "../modules/iam"
  role_name = "ecs_task_role_dev"
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
#                   ECS Task Execution Role Policy Attachments (DEV ONLY)      #
################################################################################
# NEW: Required policies for ECS task execution (pulling images, logging)
module "ecs_task_execution_policy_attachment" {
  source                      = "../modules/iam_role_policy_attachment"
  policy_attachment_role_name = module.ecs_task_execution_role.role_name
  cluster_policy_arn          = "arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy"

  depends_on = [module.ecs_task_execution_role]
}

# NEW: Allow ECS task execution role to pull from ECR
module "ecs_task_execution_ecr_policy_attachment" {
  source                      = "../modules/iam_role_policy_attachment"
  policy_attachment_role_name = module.ecs_task_execution_role.role_name
  cluster_policy_arn          = "arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryReadOnly"

  depends_on = [module.ecs_task_execution_role]
}

################################################################################
#                   ECS Task Role Policy Attachments (DEV ONLY)                #
################################################################################
# NEW: Application can access other AWS services via this role
module "ecs_task_ecr_policy_attachment" {
  source                      = "../modules/iam_role_policy_attachment"
  policy_attachment_role_name = module.ecs_task_role.role_name
  cluster_policy_arn          = "arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryReadOnly"

  depends_on = [module.ecs_task_role]
}

################################################################################
#                         ECS Cluster (DEV ONLY)                               #
################################################################################
# NEW: ECS Cluster replaces EKS cluster for DEV environment
# Benefits: Cheaper, simpler, Fargate-based (no node management)
module "ecs_cluster" {
  source       = "../modules/ecs_cluster"
  cluster_name = "my-ecs-cluster-dev"

  enable_container_insights     = true
  default_capacity_provider_base = 1

  depends_on = [module.vpc]
}

################################################################################
#                      CloudWatch Log Group (DEV ONLY)                         #
################################################################################
# NEW: Centralized logging for ECS tasks (replaces EKS logging)
module "cloudwatch_log_group" {
  source        = "../modules/cloudwatch_log_group"
  log_group_name = "/ecs/my-app-dev"
  retention_in_days = 7
}

################################################################################
#                    ECS Task Definition (DEV ONLY)                            #
################################################################################
# NEW: Task definition replaces Kubernetes deployment manifests
# Pulls image from ECR, connects to RDS, runs as containerized workload
module "ecs_task_definition" {
  source = "../modules/ecs_task_definition"

  task_family           = "my-app-dev"
  container_name        = "my-app-container"
  container_image       = var.ecs_container_image != "" ? var.ecs_container_image : module.ecr.ecr_repository_url
  container_port        = 80
  task_cpu              = var.ecs_task_cpu
  task_memory           = var.ecs_task_memory
  execution_role_arn    = module.ecs_task_execution_role.role_arn
  task_role_arn         = module.ecs_task_role.role_arn
  log_group_name        = module.cloudwatch_log_group.log_group_name
  aws_region            = "us-east-1"

  # Application environment variables (same config as EKS pods would have)
  container_environment = [
    {
      name  = "DB_HOST"
      value = module.rds_instance.db_endpoint_address
    },
    {
      name  = "DB_NAME"
      value = "my_app"
    },
    {
      name  = "DB_PORT"
      value = "3306"
    },
    {
      name  = "ENVIRONMENT"
      value = "dev"
    },
    {
      name  = "DB_USER"
      value = "admin"
    },
    {
      name  = "DB_PASS"
      value = "Admin123!"
    }
  ]

  depends_on = [
    module.ecs_task_execution_role,
    module.ecs_task_role,
    module.cloudwatch_log_group,
    module.ecr
  ]
}

################################################################################
#                    Application Load Balancer (DEV ONLY)                      #
################################################################################
# NEW: ALB provides external access to ECS tasks (replaces EKS Ingress/Service)
module "alb" {
  source = "../modules/alb"

  alb_name                   = "my-app-alb-dev"
  alb_security_group_name    = "my-app-alb-sg-dev"
  vpc_id                     = module.vpc.vpc_id
  subnet_ids                 = [module.eks_subnet_1.subnet_id, module.eks_subnet_2.subnet_id]
  internal                   = false
  enable_deletion_protection = false

  depends_on = [module.vpc, module.eks_subnet_1, module.eks_subnet_2]
}

################################################################################
#                      ALB Target Group (DEV ONLY)                             #
################################################################################
# NEW: Target group routes ALB traffic to ECS tasks
module "alb_target_group" {
  source = "../modules/alb_target_group"

  target_group_name             = "my-app-tg-dev"
  target_port                   = 80

  vpc_id                        = module.vpc.vpc_id
  health_check_healthy_threshold   = 2
  health_check_unhealthy_threshold = 2
  health_check_timeout            = 5
  health_check_interval           = 30
  health_check_path               = "/"
  health_check_matcher            = "200-299"

  depends_on = [module.vpc]
}

################################################################################
#                       ALB Listener (DEV ONLY)                                #
################################################################################
# NEW: ALB listener routes port 80 traffic to target group
module "alb_listener" {
  source = "../modules/alb_listener"

  alb_arn          = module.alb.alb_arn
  listener_port    = 80
  listener_protocol = "HTTP"
  target_group_arn = module.alb_target_group.target_group_arn

  depends_on = [module.alb, module.alb_target_group]
}

################################################################################
#                      ECS Service (DEV ONLY)                                  #
################################################################################
# NEW: ECS service replaces EKS deployment
# - Maintains 10 tasks (matching previous EKS desired_size)
# - Auto-registers tasks with ALB target group
# - Handles task lifecycle (start, stop, restart)
module "ecs_service" {
  source = "../modules/ecs_service"

  service_name      = "my-app-service-dev"
  cluster_id        = module.ecs_cluster.cluster_id
  task_definition_arn = module.ecs_task_definition.task_definition_arn
  desired_count     = var.ecs_desired_count
  vpc_id            = module.vpc.vpc_id
  subnet_ids        = [module.eks_subnet_1.subnet_id, module.eks_subnet_2.subnet_id]
  security_group_name = "ecs-tasks-sg-dev"
  container_name    = "my-app-container"
  container_port    = 80
  target_group_arn  = module.alb_target_group.target_group_arn
  alb_security_group_id = module.alb.alb_security_group_id
  assign_public_ip  = true

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
# REUSED: Same ECR repository used by ECS (was used by EKS before)
# No changes - ECS pulls images from same ECR
module "ecr" {
  source               = "../modules/ecr"
  ecr_repository_name  = "my-ecr-repo"
  image_tag_mutability = "MUTABLE"
  scan_on_push         = true

  depends_on = [module.vpc]
}

################################################################################
#                           Internet Gateway (SHARED)                          #
################################################################################
# UNCHANGED: Still needed for internet access (shared with Lambda, etc.)
module "internet_gateway" {

  source = "../modules/igw"

  vpc_id = module.vpc.vpc_id

  depends_on = [module.vpc]
}

################################################################################
#                     RouteTable & Routes (DEV - UPDATED)                      #
################################################################################
# MODIFIED: Route table now serves ECS tasks (ALB routes traffic)
# Previously served EKS cluster - same subnets, same routing logic
module "route_table" {

  source = "../modules/route_table"

  vpc_id  = module.vpc.vpc_id
  rt_name = "Internet_route_dev"

  depends_on = [module.eks_subnet_1, module.eks_subnet_2]
}

################################################################################
#                                  Route (SHARED)                              #
################################################################################
# UNCHANGED: Default route to IGW (used by ECS tasks now instead of EKS)
module "routes" {

  source = "../modules/route"

  route_table_id         = module.route_table.id
  destination_cidr_block = "0.0.0.0/0"
  gateway_id             = module.internet_gateway.igw_id

  depends_on = [module.route_table]
}

################################################################################
#                    Route Table Associations (DEV - UPDATED)                  #
################################################################################
# REUSED: Same subnets now route ECS task traffic instead of EKS control plane
module "subnet_1_rt_association" {

  source    = "../modules/route_table_association"
  subnet_id = module.eks_subnet_1.subnet_id

  route_table_id = module.route_table.id

  depends_on = [module.route_table]

}

module "subnet_2_rt_association" {

  source = "../modules/route_table_association"

  subnet_id      = module.eks_subnet_2.subnet_id
  route_table_id = module.route_table.id

  depends_on = [module.route_table]

}

################################################################################
#                           RDS Subnet Group                                   #    
################################################################################
module "rds_subnet_group" {
  source = "../modules/rds_subnet_group"
  name   = "rds-subnet-group"
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
module "rds_instance" {
  source = "../modules/rds_instance"

  identifier     = var.rds_identifier
  engine_version = "8.0"
  instance_class = "db.m5.large"

  username = var.rds_username
  password = var.rds_password
  db_name  = "my_app"

  allocated_storage     = 20
  max_allocated_storage = 90

  storage_type = "gp3"

  db_subnet_group_name = module.rds_subnet_group.subnet_group_name
  sg_id                = module.rds_sg.sg_id

  depends_on = [
    module.rds_sg,
    module.rds_subnet_group
  ]

}
