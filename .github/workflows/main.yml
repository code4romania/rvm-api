name: rvm-api-cd

on:
  push:
    branches: 
      - stage
  pull_request:
    branches:
      - stage

jobs:
  deploy:
    name: deploy-to-aws
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@stage
      if: github.event_name == 'pull_request' and github.event.action == 'closed' and github.event.pull_request.merged == 'true'
      run:
        touch ssh_key.pem
        echo $RVM_SSH_KEY > ssh_key.pem
        rsync -rv -e 'ssh -i ssh-key.pem' rvm-api $RVM_USERNAME@$RVM_MACHINE:/var/www/api
