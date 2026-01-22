################################################################################
#                    Outputs for ALB Listener Rule                             #
################################################################################

output "rule_arn" {
  description = "superviral.io path rule ka ARN"
  value       = aws_lb_listener_rule.superviral_path_rule.arn
}

output "redirect_rule_arn" {
  description = "Redirect rule ka ARN"
  value       = aws_lb_listener_rule.redirect_to_superviral.arn
}
