#!/usr/bin/env bash
set -euo pipefail

: "${DB_ROOT:?DB_ROOT must be set}"
: "${DB_USER:?DB_USER must be set}"
: "${DB_PASS:?DB_PASS must be set}"

cat > .env <<EOF
# tool hosting information
ADMIN_PORT=9090
HTTP_PORT=90
HTTPS_PORT=9443
SERVER_NAME=localhost
APP_URL=localhost

# database information
DB_HOST=127.0.0.1
DB_ROOT=${DB_ROOT}
DB_PORT=3406
DB_NAME=saperstone-studios
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}

# email information
EMAIL_CONTACT=contact@saperstonestudios.com
EMAIL_ACTIONS=actions@saperstonestudios.com
EMAIL_SELECTS=selects@saperstonestudios.com
EMAIL_CONTRACTS=contracts@saperstonestudios.com
EMAIL_HOST=saperstonestudios_mailpit
EMAIL_PORT=1025
EMAIL_USER=${EMAIL_USER:-ci}
EMAIL_PASS=${EMAIL_PASS:-dummypassword}
EMAIL_USER_X=${EMAIL_USER_X:-ci}
EMAIL_PASS_X=${EMAIL_PASS_X:-dummypassword}
EOF

chmod -R 777 content
mkdir -m 777 -p content/albums content/blog content/contracts logs tmp

# Local CI does not use the production Let's Encrypt certificate.
sed -i '/letsencrypt/ s/^/#/' .docker/php/default-ssl.conf
