#!/bin/bash

set -e -o pipefail

if [ -z "$1" ]
then
    echo "You need to supply either 'qa' or 'production' as an argument"
    exit 1
fi

set -u

DIRNAME=$(dirname $0)

echo "Deploying to ${1} with :"
echo "IMAGE_NAME: ${IMAGE_NAME}"
echo "NAMESPACE: ${NAMESPACE}"
echo "TRAEFIK_HOSTNAME: ${TRAEFIK_HOSTNAME}"
echo

# echo "${DOTENV}" > .env
## TODO dotenv-secret.yml
kustomize build ${DIRNAME}/overlays/$1 | envsubst | tee deployed.yml
# rm -f .env
# kubectl apply -f deployed.yml
