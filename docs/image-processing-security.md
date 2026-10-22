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
  (2 GiB), open files (256) and operation duration (180 seconds).
- Deliberately avoid low image width/height ceilings: camera originals can
  exceed 8,192 pixels. The size/format contract can be narrowed after a
  real inventory of legacy originals, not by silently breaking galleries.

The Dockerfile runs a smoke test in the final image build: JPEG reading/
resizing, PNG watermark composition, JPEG dimension detection must work, and
an actual MVG source must fail specifically due to the security policy. Script
tests guard policy settings and the Dockerfile enforcement.

This policy narrows the reachable vulnerable decoder/delegate surface; it
**does not establish that all image libraries are patched** or that image
parsing is free of vulnerabilities. Continue reviewing ImageMagick, zlib,
JPEG, PNG, and GIF advisories in #273 and #283. Do not wholesale dismiss
findings on the basis of this configuration.

## Verification / supported formats

- Unit/integration: file extension and byte-format consistency, JPEG/PNG/GIF
  fixtures, invalid content and pre-flight batch rejection.
- API: unauthorized upload contracts, malformed/disguised upload 400 responses,
  DB/thumbnail-status invariants, ordinary album/gallery/blog/site-image flows.
- Docker: the production image must pass the policy smoke test and container
  scan; the shared ImageMagick commands must still create thumbnails.
- Browser: existing admin/uploader album image uploads and thumbnail generation
  tests remain part of CI.
- Do not alter symlinks, protected originals in `full/`, blank/protected image
  `src` behavior or download authorization while changing image processing.

Legacy on-disk files generated before this policy may contain other formats.
If a real legacy format needs processing, identify it and add a narrow
policy/format migration with regression tests instead of removing the default
denials globally. The secure image policy applies to production Docker builds;
local PHP packages may differ until the local Docker image is rebuilt.
