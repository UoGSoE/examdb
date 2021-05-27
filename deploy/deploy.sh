#!/bin/bash

set -e -o pipefail

if [ -z "$1" ]
then
    echo "You need to supply either 'qa' or 'production' as an argument"
    exit 1
fi

set -u

echo "Deploying to ${1} with :"
echo "IMAGE_NAME: ${IMAGE_NAME}"
echo "NAMESPACE: ${NAMESPACE}"
echo "TRAEFIK_HOSTNAME: ${TRAEFIK_HOSTNAME}"
echo

echo "${DOTENV}" > .env
kustomize build overlays/$1 | envsubst | tee deployed.yml
rm -f .env
# kubectl apply -f deployed.yml
