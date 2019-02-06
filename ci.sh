#!/usr/bin/env bash

set -eu

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
LOCK=NO

for i in "$@"; do
case $i in
    --lock)
        LOCK=YES
        shift
    ;;
    *)
        # unknown option
    ;;
esac
done

run_tests_with_php() {
    echo ""
    echo "Running tests with PHP $1"
    echo ""

    sudo rm -rf $SCRIPT_DIR/vendor $SCRIPT_DIR/composer.lock

    if [ "NO" = "$LOCK" ]; then
        if [ -d $SCRIPT_DIR/vendor.php$1 ]; then
            sudo cp -rf $SCRIPT_DIR/vendor.php$1 $SCRIPT_DIR/vendor
        fi
        if [ -f $SCRIPT_DIR/composer.php$1 ]; then
            sudo cp $SCRIPT_DIR/composer.php$1 $SCRIPT_DIR/composer.lock
        fi
    fi

    $SCRIPT_DIR/phpunit.sh --php-version=$1

    sudo rm -rf $SCRIPT_DIR/vendor.php$1
    sudo cp -rf $SCRIPT_DIR/vendor $SCRIPT_DIR/vendor.php$1
    sudo cp $SCRIPT_DIR/composer.lock $SCRIPT_DIR/composer.php$1
}

run_tests_with_php 5.6
run_tests_with_php 7.0
run_tests_with_php 7.1
run_tests_with_php 7.2
