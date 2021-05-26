#!/bin/bash

set -e -u -o pipefail

if [ -z "$1" ]
then
    echo "You need to supply either 'qa' or 'production' as an argument"
    exit 1
fi

kustomize edit set image the-app=${IMAGE_NAME}
kustomize edit set namespace ${STACK_NAME}
echo $DOTENV > .env
kustomize build overlays/$1 | envsubst | tee deployed.yml
rm -f .env
# kubectl apply -f deployed.yml
