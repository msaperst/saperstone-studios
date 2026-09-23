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
    --test-reporter=junit \
    --test-reporter-destination=reports/js-junit.xml \
    --test-reporter=lcov \
    --test-reporter-destination=reports/js-lcov.info \
    tests/js/*.test.js
