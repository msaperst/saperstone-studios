#!/bin/sh
# Integration checks of the built production PHP image. Run inside that image
# as www-data, not while constructing the Docker image.
set -eu
umask 077

work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT HUP INT TERM

# CVE-2026-109618 and CVE-2026-109619 require PHP 8.4.27 or newer.
php -r '
    if (PHP_VERSION_ID < 80427 || PHP_VERSION_ID >= 80500) {
        fwrite(STDERR, "Expected patched PHP 8.4.27+; found " . PHP_VERSION . PHP_EOL);
        exit(1);
    }
    if (!extension_loaded("imagick")) {
        fwrite(STDERR, "Production Imagick extension is not enabled" . PHP_EOL);
        exit(1);
    }
    // ImageMagick 6 measures global time against a long-lived PHP process,
    // not just the current request. A short policy limit may terminate an
    // Apache worker with no HTTP response.
    $timeLimit = Imagick::getResourceLimit(Imagick::RESOURCETYPE_TIME);
    if ($timeLimit !== 0 && $timeLimit < 3600) {
        fwrite(STDERR, "Unsafe global ImageMagick time limit: " . $timeLimit . PHP_EOL);
        exit(1);
    }
'

# Production JavaScript/CSS must be generated and referenced by Strings.
php -r '
    require "/var/www/src/Strings.php";
    foreach ([
        "/js/album.js" => "/js/album.min.js",
        "/css/saperstone-studios.css" => "/css/saperstone-studios.min.css",
    ] as $source => $minified) {
        if (!is_file("/var/www/public" . $source)
            || !is_file("/var/www/public" . $minified)
            || !str_starts_with(Strings::assetUrl($source, "/var/www/public"), $minified . "?v=")) {
            fwrite(STDERR, "Invalid production asset: " . $minified . PHP_EOL);
            exit(1);
        }
    }
'

# Mirror the CLI tools used by make-thumbs.sh, not merely XML configuration.
convert /fixtures/flower.jpeg -resize '400x400>' "$work/resized.jpeg"
composite -dissolve 30 -tile /var/www/public/img/proof.png \
    "$work/resized.jpeg" "$work/resized.jpeg"
test "$(identify -format '%m %wx%h' "$work/resized.jpeg")" = 'JPEG 400x300'

# PHP Imagick must honor the same installed policy and allow normal JPEGs.
php -r '
    $image = new Imagick($argv[1]);
    if ($image->getImageFormat() !== "JPEG") {
        fwrite(STDERR, "Imagick failed to decode the expected JPEG" . PHP_EOL);
        exit(1);
    }
    $image->clear();
' "$work/resized.jpeg"

# A dangerous MVG payload must fail *because of the policy*, not because
# the decoder is missing or an unrelated image-processing operation failed.
printf 'push graphic-context\nviewbox 0 0 10 10\nrectangle 0,0 10,10\npop graphic-context\n' > "$work/payload.mvg"
if convert "MVG:$work/payload.mvg" "$work/denied.png" >"$work/mvg.log" 2>&1; then
    echo "ImageMagick unexpectedly allowed MVG decoding" >&2
    exit 1
fi
if ! grep -Eiq 'security policy|not authorized' "$work/mvg.log"; then
    cat "$work/mvg.log" >&2
    echo "MVG failed for an unexpected reason; policy not verified" >&2
    exit 1
fi

echo 'PHP image runtime, production assets, and ImageMagick policy passed'
