#!/usr/bin/env bash

set -eu

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

run_tests_with_php() {
    echo ""
    echo "Running tests with PHP $1"
    echo ""

    sudo rm -rf $SCRIPT_DIR/vendor $SCRIPT_DIR/composer.lock
    $SCRIPT_DIR/phpunit.sh --php-version=$1
}

run_tests_with_php 5.6
run_tests_with_php 7.0
run_tests_with_php 7.1
run_tests_with_php 7.2
