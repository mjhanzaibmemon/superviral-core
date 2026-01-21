terraform {
  backend "s3" {
    bucket  = "my-dev--terraform-state-bucket"
    key     = "infra/terraform.tfstate"
    region  = "us-east-2"
    encrypt = true
  }
}