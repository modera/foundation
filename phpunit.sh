#!/usr/bin/env bash

# Installs vendors, tests-runner and eventually runs tests

RUNNER_GIT_DIR="mtr"

set -e

if ! type docker > /dev/null; then
    echo "Docker is required to run tests."
    exit 1
fi

if [ ! -d "vendor" ]; then
  echo "# No vendor dir detected, installing modera/composer-monorepo-plugin and then project's dependencies"

  printf "composer global require modera/composer-monorepo-plugin:dev-master\ncomposer install" > install.sh
  chmod +x install.sh

  docker run \
  -it \
  --rm \
  -v `pwd`:/mnt/tmp \
  -w /mnt/tmp \
  modera/php7-fpm "./install.sh"

  rm install.sh
fi

if [ ! -d "$RUNNER_GIT_DIR" ]; then
  echo "# Cloning and installing test-runner"

  git clone git@github.com:modera/tests-runner.git $RUNNER_GIT_DIR

  docker run \
  -it \
  --rm \
  -v `pwd`/$RUNNER_GIT_DIR:/mnt/tmp \
  -w /mnt/tmp \
  modera/php7-fpm "composer install"
fi

# if there's no mtr_php image then create a Docker file in $RUNNER_GIT_DIR and build it

if [[ `docker ps` != *"mtr_mysql"* ]]; then
  echo "# Starting database for functional tests"
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

EXIT_CODE=$?

docker rm -f mtr_mysql > /dev/null

exit $EXIT_CODE