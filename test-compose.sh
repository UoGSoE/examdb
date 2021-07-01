#!/bin/bash

IMAGE_NAME=examdb-test:latest

docker build -t ${IMAGE_NAME} .

docker-compose -f docker-compose.test.yml up --exit-code-from test

docker-compose -f docker-compose.test.yml down --remove-orphans

