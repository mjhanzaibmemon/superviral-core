################################################################################
#                    ALB Listener Rule - superviral.io                         #
################################################################################
# Yeh module ALB listener rule banata hai jo /superviral.io/* path ko
# target group pe route karta hai.
################################################################################

# Listener Rule jo /superviral.io/* traffic ko forward karega
resource "aws_lb_listener_rule" "superviral_path_rule" {
  listener_arn = var.listener_arn
  priority     = var.priority

  # Traffic ko target group pe forward karo
  action {
    type             = "forward"
    target_group_arn = var.target_group_arn
  }

  # Path pattern match karo - /superviral.io/*
  condition {
    path_pattern {
      values = ["/superviral.io", "/superviral.io/*"]
    }
  }

  tags = merge(
    var.tags,
    {
      Name    = "superviral-io-rule-${var.env}"
      Project = "superviral.io"
    }
  )
}

# Default rule - jab koi root "/" pe aaye to /superviral.io pe redirect karo
resource "aws_lb_listener_rule" "redirect_to_superviral" {
  listener_arn = var.listener_arn
  priority     = var.redirect_priority

  # Redirect action - "/" se "/superviral.io" pe bhejo
  action {
    type = "redirect"

    redirect {
      path        = "/superviral.io"
      status_code = "HTTP_301"
    }
  }

  # Sirf root path "/" ko match karo
  condition {
    path_pattern {
      values = ["/"]
    }
  }

  tags = merge(
    var.tags,
    {
      Name    = "redirect-to-superviral-${var.env}"
      Project = "superviral.io"
    }
  )
}
