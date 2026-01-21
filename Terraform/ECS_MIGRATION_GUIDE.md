# EKS → ECS Fargate Migration - DEV Environment Only

## Overview
This Terraform configuration has been migrated from **EKS (Elastic Kubernetes Service)** to **ECS Fargate** for the DEV environment only.
This conversion maintains feature parity while reducing complexity and cost.

## Changes Summary

### ✅ What Changed (DEV ONLY)

#### Removed Resources (EKS)
- `aws_eks_cluster` - Kubernetes control plane
- `aws_eks_node_group` - Managed EC2 instances (10x t3.medium nodes)
- EKS-specific IAM roles and policies (6 policy attachments)

#### Added Resources (ECS Fargate)
- `aws_ecs_cluster` - ECS cluster (serverless, no node management)
- `aws_ecs_task_definition` - Container task definition (replaces K8s manifests)
- `aws_ecs_service` - Service managing task lifecycle (replaces K8s Deployments)
- `aws_lb` - Application Load Balancer (replaces K8s Ingress)
- `aws_lb_target_group` - Target group for routing
- `aws_lb_listener` - Port 80 listener
- `aws_cloudwatch_log_group` - Centralized logging
- ECS-specific IAM roles (task execution role + task role)

#### Reused/Modified Resources
- **VPC**: Unchanged (`10.0.0.0/16`)
- **Subnets**: Same public subnets (`10.0.2.0/24`, `10.0.1.0/24`) now host ECS tasks
- **RDS**: Completely untouched (same MySQL instance, credentials, networking)
- **Lambda**: Untouched (separate private subnet)
- **ECR**: Same repository, now used by ECS task definition
- **Internet Gateway**: Unchanged (shared infrastructure)
- **Route Tables**: Reused with updated comment (now route ECS traffic)

---

## Key Differences: EKS vs ECS Fargate

| Aspect | EKS | ECS Fargate |
|--------|-----|------------|
| **Node Management** | Manual (10x t3.medium EC2 instances) | Automatic (serverless) |
| **Orchestration** | Kubernetes control plane | AWS ECS native |
| **Task Count** | 10 nodes | 10 Fargate tasks (flexible) |
| **Task Isolation** | Pod-based | Task-based |
| **Load Balancing** | K8s Ingress/Service | AWS ALB |
| **Logging** | K8s logs → CloudWatch | Direct CloudWatch Logs |
| **Cost** | EC2 instance costs | Per-CPU/memory-per-second |
| **Complexity** | Higher (K8s knowledge required) | Lower (AWS-native) |

---

## IAM Changes

### Removed (EKS)
- `eks_cluster_role` - EKS cluster control plane role
- `eks_node_group_role` - EC2 worker node role
- All EKS-specific managed policies

### Added (ECS)
- `ecs_task_execution_role_dev` - Allows ECS to pull images, write logs (AmazonECSTaskExecutionRolePolicy)
- `ecs_task_role_dev` - Application permissions within container (can be extended with custom policies)

---

## Configuration Files Modified

### 1. `Terraform/config/main.tf`
- **Removed**: All EKS modules (cluster, node group, IAM)
- **Added**: All ECS modules (cluster, service, task definition, ALB infrastructure)
- **Updated**: IAM roles for ECS tasks
- **Comments**: Detailed inline comments explaining each section and why it changed

### 2. `Terraform/config/variable.tf`
- **Added**: ECS configuration variables
  - `ecs_task_cpu` - Task CPU units (default: 256 = 0.25 vCPU)
  - `ecs_task_memory` - Task memory in MB (default: 512)
  - `ecs_desired_count` - Number of tasks (default: 10, matching EKS nodes)
  - `ecs_container_image` - Image URI for tasks
  - `ecs_container_port` - Container listening port

### 3. `Terraform/modules/ecr/output.tf`
- **Added**: `ecr_repository_url` output for ECS task definition to reference

### 4. `Terraform/modules/rds_instance/output.tf`
- **Added**: `db_endpoint_address` output for ECS task environment variables

### 5. New ECS Modules Created
- `modules/ecs_cluster/` - ECS cluster definition
- `modules/ecs_task_definition/` - Container task specification
- `modules/ecs_service/` - Service lifecycle management
- `modules/alb/` - Application Load Balancer
- `modules/alb_target_group/` - Target group for ALB
- `modules/alb_listener/` - ALB listener rules
- `modules/cloudwatch_log_group/` - Log group for container logs

---

## Environment Variables Passed to Tasks

The ECS task definition automatically injects these environment variables into containers:

```bash
DB_HOST=<rds-endpoint>        # MySQL database host
DB_NAME=my_app                # Database name
DB_PORT=3306                  # Database port
ENVIRONMENT=dev               # Environment identifier
```

These replace what would have been set in Kubernetes ConfigMaps.

---

## Networking Behavior (Unchanged)

- **Public Subnets**: Tasks run in same public subnets as EKS did (10.0.2.0/24, 10.0.1.0/24)
- **Internet Access**: Via same Internet Gateway
- **RDS Access**: Tasks can connect to RDS in private subnets (security group allows all VPC CIDR)
- **Load Balancing**: ALB routes incoming traffic on port 80 → tasks on port 8080
- **Task Security Group**: Auto-created, allows ingress from ALB only

---

## Scaling Configuration

### Before (EKS)
```
Node Group: desired_size=10, max_size=20, min_size=1
Each node runs multiple pods
```

### After (ECS)
```
Service: desired_count=10
Each Fargate task runs independently
```

**Note**: ECS auto-scaling can be added later by creating `aws_appautoscaling_target` and `aws_appautoscaling_policy` resources.

---

## Deployment Instructions

### Prerequisites
1. Ensure all ECS module files exist in `Terraform/modules/`
2. Verify AWS credentials for DEV environment are set
3. Have an ECR image URI ready or push image to `my-ecr-repo`

### Steps

1. **Initialize Terraform** (if first time or new modules added):
   ```bash
   terraform -chdir=Terraform/config init
   ```

2. **Review Changes**:
   ```bash
   terraform -chdir=Terraform/config plan
   ```
   
   **Expected Output**: 
   - Resources to create: All ECS resources (~15 resources)
   - Resources to delete: All EKS resources (~5 resources)
   - Resources unchanged: VPC, RDS, subnets, etc.

3. **Apply Changes**:
   ```bash
   terraform -chdir=Terraform/config apply
   ```
   
   This will:
   - Destroy EKS cluster and node group
   - Create ECS cluster and launch 10 tasks
   - Create ALB and configure routing
   - Provision CloudWatch logs

4. **Verify Deployment**:
   ```bash
   # List ECS tasks
   aws ecs list-tasks --cluster my-ecs-cluster-dev --region us-east-2
   
   # Get ALB DNS name
   aws elbv2 describe-load-balancers --query 'LoadBalancers[?LoadBalancerName==`my-app-alb-dev`].DNSName' --region us-east-2
   
   # View task logs
   aws logs tail /ecs/my-app-dev --follow --region us-east-2
   ```

---

## Rollback Instructions (If Needed)

To revert to EKS:

1. Restore the original `Terraform/config/main.tf` from git history
2. Restore the original `Terraform/config/variable.tf`
3. Run:
   ```bash
   terraform -chdir=Terraform/config plan
   terraform -chdir=Terraform/config apply
   ```

The ECS resources will be destroyed and EKS will be recreated.

---

## Troubleshooting

### Tasks Not Running
- Check task definition: `aws ecs describe-task-definition --task-definition my-app-dev --region us-east-2`
- Check service: `aws ecs describe-services --cluster my-ecs-cluster-dev --services my-app-service-dev --region us-east-2`
- View logs: `aws logs tail /ecs/my-app-dev --follow --region us-east-2`

### Tasks Unhealthy
- Check health check configuration in target group
- Ensure container is listening on port 8080
- Verify security group allows traffic from ALB

### Cannot Connect to RDS
- Verify RDS security group allows traffic from VPC CIDR (10.0.0.0/16)
- Check DB_HOST environment variable matches RDS endpoint
- Test: `mysql -h <DB_HOST> -u <username> -p <password> -D my_app`

### ECR Image Pull Failures
- Verify image URI is correct in task definition
- Ensure ECR repository contains the image
- Check task execution role has `AmazonEC2ContainerRegistryReadOnly` policy

---

## Production & Staging

⚠️ **IMPORTANT**: This migration is **DEV environment ONLY**.
- Production (main branch) remains on EKS
- Staging (stage branch) remains on EKS
- Each environment has separate AWS credentials and Terraform state
- No impact to production or staging workloads

---

## Cost Comparison (Rough Estimate)

### EKS (Previous)
- 10x t3.medium EC2 instances: ~$100/month
- EKS control plane: $0.10/hour (~$72/month)
- **Total**: ~$172/month

### ECS Fargate (New)
- 10 tasks × (0.25 vCPU + 512MB) × 730 hours: ~$45/month
- ALB: ~$20/month
- **Total**: ~$65/month

**Estimated Savings**: ~60% reduction in compute costs for DEV

---

## Support & Questions

For questions about this migration:
1. Review inline comments in `Terraform/config/main.tf`
2. Check module documentation in each `modules/*/README.md` (if exists)
3. Refer to AWS ECS Fargate documentation
4. Review GitHub Actions workflows in `.github/workflows/`

---

**Migration Completed**: January 14, 2026
**Environment**: DEV only
**Status**: Ready for testing and deployment
