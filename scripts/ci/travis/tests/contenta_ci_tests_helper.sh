#!/usr/bin/env bash

COMPOSER_BIN_DIR="$(composer config bin-dir)"
DOCROOT="web"

# Run Functional Tests Function
# Run Contenta CMS Functional Tests
# This function receives one argument:
#   $1 -> The Drupal Base Path
run_functional_tests() {
    if [ -z $1 ] ; then
        echo "Please pass the Contenta Project Base Path to the run_functional_tests function " 1>&2
        exit 1
    fi

    if [[ -z $SIMPLETEST_BASE_URL ]] ; then
        echo "Please ensure that SIMPLETEST_BASE_URL environment variable is set. Ex: http://localhost" 1>&2
        exit 1
    fi
    if [[ -z $SIMPLETEST_DB ]] ; then
        echo "Please ensure that SIMPLETEST_DB environment variable is set. Ex: mysql://username:password@localhost/databasename#table_prefix" 1>&2
        exit 1
    fi

    current_path=`pwd`
    CONTENTA_PATH=$1/$DOCROOT/profiles/contrib/contenta_jsonapi/
    PHPUNIT=$1/$COMPOSER_BIN_DIR/phpunit

    cd $CONTENTA_PATH

    cat ${CONTENTA_PATH}phpunit.xml
    echo "SIMPLETEST_BASE_URL=$SIMPLETEST_BASE_URL SIMPLETEST_DB=$SIMPLETEST_DB ${PHPUNIT} -c ${CONTENTA_PATH}phpunit.xml --testsuite ContentaFunctional"
    SIMPLETEST_BASE_URL=$SIMPLETEST_BASE_URL SIMPLETEST_DB=$SIMPLETEST_DB ${PHPUNIT} -c ${CONTENTA_PATH}phpunit.xml --testsuite ContentaFunctional
    exit $?
}

$@
