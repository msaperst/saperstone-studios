#!/bin/sh
# Run on the production Docker host to detect deployed-image drift.
set -eu
IMAGE="${1:-ghcr.io/msaperst/saperstone-studios/php:develop}"
CONTAINER="${2:-saperstonestudios_php}"

docker pull "$IMAGE" >/dev/null
running_image=$(docker inspect --format '{{.Image}}' "$CONTAINER")
published_image=$(docker image inspect --format '{{.Id}}' "$IMAGE")

echo "Running container image: $running_image"
echo "Published image:         $published_image"
docker exec "$CONTAINER" php -v | head -n 1
if [ "$running_image" != "$published_image" ]; then
    echo 'ERROR: Production PHP container does not match the latest published image. Redeploy and verify.' >&2
    exit 1
fi
printf '%s\n' 'Production PHP container matches the published image.'
