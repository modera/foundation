#!/usr/bin/env bash

# Experimental, for internal use only

# Internlinks nested composer packages and runs tests
# Sample usage:
## Run tests for all packages, before running tests create a temporary MySQL container:
# ./phpunit.sh --path=src --functional
## This will only run "foo/bar" package's tests without bootstrapping a temporary MySQL container and linking it with the runner:
# ./phpunit.sh --path=src --package=foo/bar

RUNNER_GIT_DIR=".mtr"

set -e

if ! type docker > /dev/null; then
    echo "Docker is required to run tests."
    exit 1
fi

if [ ! -d "$RUNNER_GIT_DIR" ]; then
  git clone git@bitbucket.org:moderasoftware/monolithic-repository-tests-runner.git $RUNNER_GIT_DIR
fi

if [[ "$(docker images -q modera/mtr 2> /dev/null)" == "" ]]; then
  ROOT_DIR=`pwd`

  cd $RUNNER_GIT_DIR
  ./install.sh

  cd $ROOT_DIR
fi

ARGS=$@

if [[ "$ARGS" == "" ]]; then
  echo ""
  echo "No args were provided, running tests for all available packages in src/foundation with a temporary MySQL database."
  echo "Beware, this might take quite a while! You have 15 seconds to abort by doing CTRL+C, waiting ..."
  echo ""

  sleep 15

  ARGS="--path=src --functional"
fi

docker run \
    -it \
    --rm \
    -e CWD=`pwd` \
    -v /var/run/docker.sock:/var/run/docker.sock \
    -v `which docker`:/usr/bin/docker \
    -v `pwd`:/mnt/tmp \
    -w /mnt/tmp \
    modera/mtr $ARGS