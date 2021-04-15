#!/bin/bash


docker run --rm -p 3333:3306 \
  -d \
  -e MYSQL_ROOT_PASSWORD=finger \
  -e MYSQL_USER=homestead \
  -e MYSQL_PASSWORD=secret \
  -e MYSQL_DATABASE=examdb_test \
  --name examdb_test_sql \
  --ulimit nofile=65000:65000 \
  --tmpfs=/var/lib/mysql/:rw,noexec,nosuid,size=600m \
  --tmpfs=/tmp/:rw,noexec,nosuid,size=50m \
  mariadb:10 --max_connections=1000 >> /dev/null

until mysql -uroot -h127.0.0.1 -pfinger -P 3333 examdb_test -e 'select 1'
do
  echo "Waiting for database connection..."
  sleep 5
done

ulimit -n 4000
CI=1 phpunit
EXITCODE=$?
docker stop examdb_test_sql >> /dev/null

exit $EXITCODE

