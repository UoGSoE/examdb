#!/bin/bash

docker run -d -p 9000:9000 \
  --name minio_examdb \
  -e "MINIO_ACCESS_KEY=AKIAIOSFODNN7EXAMPLE" \
  -e "MINIO_SECRET_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY" \
  minio/minio server /data

sleep 10

docker exec minio_examdb mkdir -p /data/exampapers

