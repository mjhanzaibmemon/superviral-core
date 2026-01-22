resource "aws_cloudwatch_log_group" "this" {
  name              = var.log_group_name
  retention_in_days = var.retention_in_days

  # Skip destroy = false means it WILL be deleted on terraform destroy
  skip_destroy = false

  tags = {
    Name = var.log_group_name
  }
}
