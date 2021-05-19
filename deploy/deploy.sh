#!/bin/bash

kustomize edit set image the-app=${IMAGE_NAME}
kustomize edit set namespace ${STACK_NAME}
