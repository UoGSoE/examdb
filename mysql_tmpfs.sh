#!/bin/bash

docker run --rm -p 3333:3306 \
  -e MYSQL_ROOT_PASSWORD=finger \
  -e MYSQL_USER=homestead \
  -e MYSQL_PASSWORD=secret \
  -e MYSQL_DATABASE=examdb_test \
  --name examdb_test_sql \
  --ulimit nofile=65000:65000 \
  --tmpfs=/var/lib/mysql/:rw,noexec,nosuid,size=600m \
  --tmpfs=/tmp/:rw,noexec,nosuid,size=50m \
  mariadb:10 --max_connections=1000
