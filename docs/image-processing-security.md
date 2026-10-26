# Image ingestion and ImageMagick security contract

This site handles large professional photographs and keeps private full-resolution
album originals protected. Restricting image decoders must not weaken album image
authorization, protected previews, or controlled downloads.

## Entry points and authorization

| Entry point | Caller | Processing |
| --- | --- | --- |
| `api/upload-album-images.php` | Admin or owning album uploader (via `Album::canUserGetData()`) | Store original, read image dimensions for DB; later thumbnail generation |
| `api/upload-gallery-images.php` | Admin | Store; resize/crop via `File` / `ImageProcessor`; add to DB |
| `api/upload-blog-images.php` | Admin | Store in temporary media tree; resize |
| `api/upload-image.php`, `api/crop-image.php` | Admin | Replace/crop public site image with storage-root path validation |
| `api/make-thumbs.php` | Admin or owning uploader | Authorize requested album; launch `bin/make-thumbs.sh` with integer album ID and fixed markup/mode values |
| Blog preview generation | Admin-only authoring workflow | Copy and resize a selected media image |

Every `File::upload()` now validates **all members of the batch prior to
moving any file**. The server checks the filename extension AND actual
`getimagesize()` format against JPEG, PNG, or GIF. It does not trust browser
`Content-Type` or merely the filename. Mixed formats with misleading
extensions, malformed content and non-image formats are rejected (HTTP 400)
without writing them or creating album image records.

The format check is a header/metadata validation, not proof that every pixel
of the file is well formed; downstream decoding remains security sensitive.

## Shared ImageMagick 6 production policy

`.docker/php/image-processing-policy.xml` replaces the default ImageMagick
policy in the **production PHP image**. Both PHP Imagick and the CLI tools
(`convert`, `composite`, `identify`) use it.

- Deny all image modules by default; allow only **GIF, JPEG, PNG** reading/writing.
- Block external delegates, filters and indirect `@file` / pipe reads. In
  particular, MVG, MSL, SVG, EPS, PDF, and remote URL coders cannot process.
- Cap threads (2), pixel-cache RAM (256 MiB), mmap (512 MiB), on-disk cache
  (2 GiB), and open pixel-cache files (256).
- **Do not use a global ImageMagick time limit in the mod_php image.**
  ImageMagick 6 measures elapsed process time; an Apache PHP worker remains
  alive across requests. A finite policy timeout can abruptly terminate it
  while processing an otherwise ordinary image, producing HTTP disconnects
  (Guzzle cURL error 52) rather than a catchable exception. Timeouts for
  individual thumbnail commands should use subprocess-level control instead
  of a shared ImageMagick policy.
- Deliberately avoid low image width/height ceilings: camera originals can
  exceed 8,192 pixels. The size/format contract can be narrowed after a
  real inventory of legacy originals, not by silently breaking galleries.

The Dockerfile **only installs the policy**; it does not execute tests.
The existing PHP Container Scan CI job builds the production image and then
invokes `tests/container/run-php-runtime-security.sh`. Its in-container
integration suite (`php-runtime-security.sh`) runs as `www-data` and
verifies the actual built image: patched PHP runtime, enabled Imagick,
production assets, a safe ImageMagick process time limit, JPEG
reading/resizing, tiled PNG proof composition, and an actual MVG source
rejected specifically by the security policy.
The script tests verify policy configuration and enforce this separation.
These checks do not depend on starting the web application or database.

This policy narrows the reachable vulnerable decoder/delegate surface; it
**does not establish that all image libraries are patched** or that image
parsing is free of vulnerabilities. Continue reviewing ImageMagick, zlib,
JPEG, PNG, and GIF advisories in #273 and #283. Do not wholesale dismiss
findings on the basis of this configuration.

## Newly disclosed bzip2 image-input vulnerability (October 26, 2026)

The October 26 [published-image security watch](https://github.com/msaperst/saperstone-studios/actions/runs/40385972776)
identified six package findings for one root vulnerability,
[`CVE-2026-116149`](https://security-tracker.debian.org/tracker/CVE-2026-116149):
an ImageMagick 6 stack overwrite in its bzip2 compressed-image blob reader.
The upstream 6.9.13-58 release fixes it; Debian Bookworm had no fixed
package at the time of this review.

**Important policy limitation:** the JPEG/PNG/GIF ImageMagick module allowlist
does **not** necessarily prevent transparent bzip2 decompression. The image
library can recognize the `BZh` magic before selecting an allowed image
decoder, including when a compressed image is renamed to an allowed filename.
Do not assume an allowed decoder module implies a safe blob reader.

Until the Debian patch arrives, the site uses layered input restrictions:

- New uploads must match real JPEG/PNG/GIF data. The PHP upload validator's
  byte-format check rejects compressed files before they are stored.
- `ImageProcessor` skips inputs beginning with bzip2 magic (`BZh`) before
  constructing an Imagick instance. It retains the existing nonfatal
  behavior for unsupported images.
- The thumbnail shell worker rejects stored/legacy full-resolution originals
  with the `BZh` signature before invoking any `convert` command, leaving
  the protected source intact and providing an explicit error status.
- The ImageMagick policy denies paths ending in `.bz` / `.bz2` (including
  case variants). This is **not** sufficient by itself for compressed data
  disguised with a different extension, hence the entry-point checks.

The production-image runtime suite tests normal image operations and the
compressed-GIF rejection, including magic-disguised input to
`ImageProcessor`; the API suite verifies rejected legacy originals leave
thumbnail data unmodified. Script Testing validates that the compressed
fixture is a real GIF. All tests run outside the Dockerfile.

**Residual risk:** These mitigations harden known application entry points
but do **not** patch ImageMagick itself, nor prove that every possible
library invocation is unreachable. Keep the CVE tracked in #283/#273,
monitor Debian's patched package, and prefer installing the vendor fix once
available. Do not add a broad Grype suppression.

## Verification / supported formats

- Unit/integration: file extension and byte-format consistency, JPEG/PNG/GIF
  fixtures, invalid content and pre-flight batch rejection.
- API: unauthorized upload contracts, malformed/disguised upload 400 responses,
  DB/thumbnail-status invariants, ordinary album/gallery/blog/site-image flows.
- Runtime image integration: run `sh tests/container/run-php-runtime-security.sh
  saperstone-studios/php:latest` after building the image. The suite tests
  actual PHP, asset, and ImageMagick behavior; container scanning remains
  separately enforced. The shared ImageMagick commands must still create
  thumbnails.
- Browser: existing admin/uploader album image uploads and thumbnail generation
  tests remain part of CI.
- Do not alter symlinks, protected originals in `full/`, blank/protected image
  `src` behavior or download authorization while changing image processing.

Legacy on-disk files generated before this policy may contain other formats.
If a real legacy format needs processing, identify it and add a narrow
policy/format migration with regression tests instead of removing the default
denials globally. The secure image policy applies to production Docker builds;
local PHP packages may differ until the local Docker image is rebuilt.
