#!/usr/bin/env bash

RUNNER_GIT_DIR="mtr"

if ! type docker > /dev/null; then
    echo "Docker is required to run tests."
    exit 1
fi

if [ ! -d "vendor" ]; then
  composer install
fi

if [ ! -d "$RUNNER_GIT_DIR" ]; then
  git clone git@bitbucket.org:moderasoftware/tests-runner.git $RUNNER_GIT_DIR
fi

if [[ `docker ps` != *"mtr_mysql"* ]]; then
  docker run -d -e MYSQL_ROOT_PASSWORD=123123 --name mtr_mysql mysql:5 > /dev/null
fi

docker run \
-it \
--rm \
-v `pwd`:/mnt/tmp \
-w /mnt/tmp \
--link mtr_mysql:mysql \
modera/php7-fpm \
bash -c "vendor/bin/phpunit"

docker rm -f mtr_mysql > /dev/null