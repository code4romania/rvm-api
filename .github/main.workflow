workflow "Build and deploy" {
  on = "push"
  resolves = ["contention/rsync-deployments@master"]
}

workflow "Build and deploy for PR" {
  on = "pull_request"
  resolves = ["contention/rsync-deployments@master-1"]
}

action "Filters for GitHub Actions - branch" {
  uses = "actions/bin/filter@master"
  args = "branch stage"
}

action "MilesChou/composer-action@master" {
  uses = "MilesChou/composer-action@master"
  needs = ["Filters for GitHub Actions - branch"]
  args = "install"
}

action "contention/rsync-deployments@master" {
  uses = "contention/rsync-deployments@master"
  needs = ["MilesChou/composer-action@master"]
  secrets = ["RVM_MACHINE", "RVM_USERNAME", "DEPLOY_KEY"]
  args = "\"-rv\", \"--exclude /dev/ --exclude .gitignore\", \"$RVM_USERNAME@$RVM_MACHINE:/var/www/api\""
}

action "Filters for GitHub Actions-1" {
  uses = "actions/bin/filter@master"
  args = "branch stage"
}

action "Filters for GitHub Actions-2" {
  uses = "actions/bin/filter@25b7b846d5027eac3315b50a8055ea675e2abd89"
  needs = ["Filters for GitHub Actions-1"]
  args = "merged true"
}

action "MilesChou/composer-action" {
  uses = "MilesChou/composer-action@master"
  needs = ["Filters for GitHub Actions-2"]
  args = "install"
}

action "contention/rsync-deployments@master-1" {
  uses = "contention/rsync-deployments@master"
  needs = ["MilesChou/composer-action"]
  secrets = ["DEPLOY_KEY", "RVM_MACHINE", "RVM_USERNAME"]
  args = "\"-rv\", \"--exclude /dev/ --exclude .gitignore\", \"$RVM_USERNAME@$RVM_MACHINE:/var/www/api\""
}
