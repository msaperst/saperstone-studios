#!/bin/bash

set -euo pipefail

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT="$( dirname "${DIR}" )"

cd "${ROOT}"
mkdir -p reports
rm -f reports/js-junit.xml reports/js-lcov.info

node --test \
    --experimental-test-coverage \
    --test-coverage-include='public/js/*.js' \
    --test-coverage-exclude='public/js/jqBootstrapValidation.js' \
    --test-coverage-exclude='public/js/jquery.form.min.js' \
    --test-coverage-exclude='public/js/jquery.uploadfile.js' \
    --test-reporter=spec \
    --test-reporter-destination=stdout \
    --test-reporter=junit \
    --test-reporter-destination=reports/js-junit.xml \
    --test-reporter=lcov \
    --test-reporter-destination=reports/js-lcov.info \
    tests/js/*.test.js
