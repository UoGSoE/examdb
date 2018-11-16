#!/bin/bash

set -e

APP_NAME=examdb

if [ -z "${STAGING_SERVER}" ];
then
    echo "Error: No STAGING_SERVER set"
    exit 1
fi

BRANCH=${BRANCH:-master}
ssh billy@"${STAGING_SERVER}" APP_NAME=${APP_NAME} "cd docker-apps/$APP_NAME && git pull origin ${BRANCH} && docker-compose build"
