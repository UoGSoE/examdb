#!/bin/bash

set -e

APP_NAME=examdb

if [ -z "${STAGING_SERVER}" ];
then
    echo "Error: No STAGING_SERVER set"
    exit 1
fi

BRANCH=${BRANCH:-master}
LOCAL_IP=`dscacheutil -q host -a name ${STAGING_SERVER} | grep ip_address | cut -f2 -d' '`
ssh billy@"${STAGING_SERVER}" APP_NAME=${APP_NAME} LOCAL_IP=${LOCAL_IP} "cd docker-apps/$APP_NAME && git pull origin ${BRANCH} && docker-compose build && docker-compose push && docker stack deploy -c docker-compose.yml $APP_NAME"
