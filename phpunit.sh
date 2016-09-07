#!/usr/bin/env bash

# Installs vendors, tests-runner and eventually runs tests
#
# If you need to run tests often then you can use "--md" configuration parameter and script then will not
# terminate MySQL container after the tests run, for example:
# $ ./phpunit.sh --md
# Importantly, "--md" argument must always be specified in a first because all arguments given afterwards are
# passed to PHPUnit as is. For example, this will run tests of src/Foo/Bar directory:
# $ ./phpunit.sh --md src/Foo/Bar

RUNNER_GIT_DIR="mtr"

set -e

args=$@
is_daemon=false

if [[ ${args:0:4} == "--md" ]]; then
  args=${args:4}
  is_daemon=true
fi

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

  git clone https://github.com/modera/tests-runner.git $RUNNER_GIT_DIR

  docker run \
  -it \
  --rm \
  -v `pwd`/$RUNNER_GIT_DIR:/mnt/tmp \
  -w /mnt/tmp \
  modera/php7-fpm "composer install"
fi

# if there's no mtr_php image then create a Docker file in $RUNNER_GIT_DIR and build it

if [[ `docker ps` != *"mtr_mysql"* ]]; then
  if [ "$is_daemon" = true ] ; then
    echo "# Starting database for functional tests (as daemon)"
  else
    echo "# Starting database for functional tests"
  fi

  docker run -d -e MYSQL_ROOT_PASSWORD=123123 --name mtr_mysql mysql:5 > /dev/null
else
  echo "# MySQL container is already running, reusing it"
fi

echo ""

# MONOLITH_TEST_SUITE env variable is used by FunctionalTestClass
docker run \
-it \
--rm \
-v `pwd`:/mnt/tmp \
-w /mnt/tmp \
-e MONOLITH_TEST_SUITE=1 \
--link mtr_mysql:mysql \
modera/php7-fpm "vendor/bin/phpunit ${args}"

exit_code=$?

if [ "$is_daemon" = false ] ; then
  docker rm -f mtr_mysql > /dev/null
fi

exit $exit_code