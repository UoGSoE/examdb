#!/bin/bash

if [ -z "$1" ];
then
    IMAGE_NAME="examdb-test:latest"
    docker build -t ${IMAGE_NAME} .
else
    IMAGE_NAME="$1"
fi

export IMAGE_NAME
echo "Testing using container image $IMAGE_NAME"

docker-compose -f docker-compose.test.yml up --exit-code-from test

docker-compose -f docker-compose.test.yml down --remove-orphans
