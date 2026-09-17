#!/usr/bin/env bash
set -euo pipefail

REPOSITORY="msaperst/saperstone-studios"
WORKFLOW="build-and-deploy.yml"
BRANCH="develop"
DEPLOY_DIR="/home/dietpi"
STATE_DIR="/var/lib/saperstone-deploy"
STATE_FILE="${STATE_DIR}/last-deployed-sha"

latest_sha="$({
  curl -fsSL \
    -H 'Accept: application/vnd.github+json' \
    "https://api.github.com/repos/${REPOSITORY}/actions/workflows/${WORKFLOW}/runs?branch=${BRANCH}&status=success&per_page=1"
} | python3 -c 'import json, sys; runs=json.load(sys.stdin).get("workflow_runs", []); print(runs[0]["head_sha"] if runs else "")')"

if [[ -z "${latest_sha}" ]]; then
  echo "No successful ${WORKFLOW} run found for ${BRANCH}; nothing to deploy."
  exit 0
fi

current_sha=""
if [[ -f "${STATE_FILE}" ]]; then
  current_sha="$(cat "${STATE_FILE}")"
fi

if [[ "${latest_sha}" == "${current_sha}" ]]; then
  echo "${latest_sha} is already deployed."
  exit 0
fi

echo "Deploying successful build ${latest_sha}."
cd "${DEPLOY_DIR}"
docker compose pull
docker compose up -d

mkdir -p "${STATE_DIR}"
printf '%s\n' "${latest_sha}" > "${STATE_FILE}"
echo "Deployment complete: ${latest_sha}."
