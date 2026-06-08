#!/usr/bin/env bash

set -euo pipefail

if [ "$#" -lt 4 ]; then
  echo "Usage: $0 <resource-group> <acr-name> <image-name> <image-tag>"
  exit 1
fi

RESOURCE_GROUP="$1"
ACR_NAME="$2"
IMAGE_NAME="$3"
IMAGE_TAG="$4"

az acr build \
  --resource-group "$RESOURCE_GROUP" \
  --registry "$ACR_NAME" \
  --image "${IMAGE_NAME}:${IMAGE_TAG}" \
  .
