################################################################################
#                    Variables for ALB Listener Rule                           #
################################################################################

variable "listener_arn" {
  description = "ALB Listener ka ARN"
  type        = string
}

variable "target_group_arn" {
  description = "Target Group ka ARN jahan traffic jayega"
  type        = string
}

variable "priority" {
  description = "Rule priority (lower = higher priority)"
  type        = number
  default     = 100
}

variable "redirect_priority" {
  description = "Redirect rule ki priority"
  type        = number
  default     = 200
}

variable "env" {
  description = "Environment name (dev/stage/prod)"
  type        = string
  default     = "dev"
}

variable "tags" {
  description = "Tags for resources"
  type        = map(string)
  default     = {}
}
