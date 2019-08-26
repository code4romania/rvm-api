workflow "Build and deploy" {
  on = "push"
  resolves = ["MilesChou/composer-action@stage"]
}

action "MilesChou/composer-action@stage" {
  uses = "MilesChou/composer-action@stage"
  args = "install --prefer-dist"
}
