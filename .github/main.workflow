workflow "Build and deploy" {
  on = "push"
  resolves = ["MilesChou/composer-action@stage"]
}

action "MilesChou/composer-action@stage" {
  uses = "MilesChou/composer-action@stage"
  args = "install --prefer-dist"
}

workflow "Build and deploy for PR" {
  resolves = ["Filters for GitHub Actions"]
  on = "pull_request"
}

action "Filters for GitHub Actions" {
  uses = "MilesChou/composer-action@stage"
  args = "install --prefer-dist"
}
