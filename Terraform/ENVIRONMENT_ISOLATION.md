# Environment Isolation Strategy

## Overview
This document explains how we prevent conflicts when promoting code between dev → stage → prod environments.

## 🔐 Key Protection Mechanisms

### 1. **Separate State Files**
Each environment uses its own S3 bucket and state file path:
```
dev:   s3://superviral-tf-state-dev/infra/dev/terraform.tfstate
stage: s3://superviral-tf-state-stage/infra/stage/terraform.tfstate
prod:  s3://superviral-tf-state-prod/infra/prod/terraform.tfstate
```

### 2. **DynamoDB State Locking**
Prevents concurrent modifications:
- Same person cannot deploy to multiple environments simultaneously
- Prevents race conditions during parallel deployments
- Auto-releases lock after deployment completes

Configure in `backend.tf` during init:
```bash
terraform init \
  -backend-config="dynamodb_table=terraform-state-lock-${environment}"
```

### 3. **Environment-Specific Resource Naming**
All resources include `${var.environment}` suffix:
```
vpc:         superviral-vpc-dev
ecs-service: superviral-ecs-service-dev
alb:         superviral-alb-dev
rds:         superviral-dev-db
```

This prevents resource name collisions across environments.

### 4. **Separate AWS Accounts/Credentials** (Recommended)
Current setup uses:
- Different AWS credentials per environment
- Different secrets in AWS Secrets Manager
- Different ECR repositories (can be shared)

**Best Practice**: Use separate AWS accounts for prod vs non-prod

### 5. **Environment Variable Validation**
The `var.environment` variable must be one of: `dev`, `stage`, `prod`

Add to `variable.tf`:
```terraform
variable "environment" {
  type        = string
  description = "Environment name"
  
  validation {
    condition     = contains(["dev", "stage", "prod"], var.environment)
    error_message = "Environment must be dev, stage, or prod"
  }
}
```

### 6. **Production Safeguards**
- `prevent_destroy` lifecycle rule on RDS/Redis in prod
- Manual approval required for prod deployments (GitHub Actions)
- Longer health check times in prod for stability

## 🚀 Deployment Workflow

### Dev Environment
```bash
cd Terraform/config
terraform init \
  -backend-config="bucket=superviral-tf-state-dev" \
  -backend-config="key=infra/dev/terraform.tfstate" \
  -backend-config="region=us-east-1" \
  -backend-config="dynamodb_table=terraform-state-lock-dev"

terraform plan -var="environment=dev"
terraform apply -var="environment=dev"
```

### Stage Environment
```bash
terraform init \
  -backend-config="bucket=superviral-tf-state-stage" \
  -backend-config="key=infra/stage/terraform.tfstate" \
  -backend-config="region=us-east-1" \
  -backend-config="dynamodb_table=terraform-state-lock-stage"

terraform plan -var="environment=stage"
terraform apply -var="environment=stage"
```

### Production Environment
```bash
terraform init \
  -backend-config="bucket=superviral-tf-state-prod" \
  -backend-config="key=infra/prod/terraform.tfstate" \
  -backend-config="region=us-east-1" \
  -backend-config="dynamodb_table=terraform-state-lock-prod"

terraform plan -var="environment=prod"
terraform apply -var="environment=prod"
```

## ⚠️ Common Pitfalls to Avoid

### ❌ DON'T:
1. Use same state file for multiple environments
2. Hardcode resource names without environment suffix
3. Share secrets between environments
4. Deploy to wrong environment (always double-check!)
5. Skip `terraform plan` before `apply`

### ✅ DO:
1. Always verify `var.environment` before apply
2. Use separate AWS accounts for prod (ideal)
3. Enable DynamoDB locking
4. Review terraform plan output carefully
5. Tag all resources with environment name

## 🔍 Verification Commands

Check current environment:
```bash
aws sts get-caller-identity  # Verify AWS account
terraform workspace show     # If using workspaces
terraform show | grep environment
```

Check state file location:
```bash
terraform state pull | jq '.backend'
```

## 📋 Pre-Deployment Checklist

- [ ] Correct AWS credentials set?
- [ ] Backend config points to right state file?
- [ ] `environment` variable set correctly?
- [ ] Reviewed `terraform plan` output?
- [ ] DynamoDB lock table exists?
- [ ] No manual changes in AWS console?

## 🛡️ Emergency Procedures

If state file gets corrupted:
```bash
# List backups
aws s3 ls s3://superviral-tf-state-${env}/infra/${env}/

# Restore from backup
terraform state pull > backup-$(date +%Y%m%d).tfstate
```

If deployment stuck on lock:
```bash
# Check lock
aws dynamodb get-item \
  --table-name terraform-state-lock-${env} \
  --key '{"LockID":{"S":"superviral-tf-state-bucket/infra/${env}/terraform.tfstate-md5"}}'

# Force unlock (use carefully!)
terraform force-unlock <LOCK_ID>
```

## 📚 Additional Resources

- [Terraform Best Practices](https://www.terraform-best-practices.com/)
- [AWS Multi-Account Strategy](https://aws.amazon.com/organizations/getting-started/best-practices/)
- [State File Security](https://www.terraform.io/docs/language/state/sensitive-data.html)
