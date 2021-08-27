#!/bin/bash

set -euo pipefail

export IMAGE_NAME=uogsoe/examdb-test:0.1
export TRAEFIK_HOSTNAME=foobarzy.net
export DOTENV=.env.single-server

NOW=`date +%Y-%m-%d-%H-%M-%S`
export DOTENV_NAME="examdb-prod-dotenv-${NOW}"
echo "Deploying stack examdb with image ${IMAGE_NAME} and secret ${DOTENV_NAME}"
echo "Hostname will be ${TRAEFIK_HOSTNAME}"

cat "${DOTENV}" | docker secret create ${DOTENV_NAME} -
docker stack deploy -c single-server-stack.yml,single-server-stack-demo.yml --prune examdb
docker/docker-stack-wait.sh examdb
