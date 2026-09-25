# Album thumbnail generation

Album previews use responsive, protected derivatives rather than sending full-resolution originals to the browser.

## File layout

Before thumbnails have ever been generated, uploaded originals live in the album root:

```text
albums/<album>/
  image.jpg
```

On first thumbnail generation the original is preserved in `full/`. The root path becomes the largest protected web derivative for backwards compatibility:

```text
albums/<album>/
  full/image.jpg          # original; source of truth
  thumbs/400/image.jpg    # max long edge 400px
  thumbs/800/image.jpg    # max long edge 800px
  thumbs/1200/image.jpg   # max long edge 1200px
  image.jpg               # max long edge 1600px
```

Every derivative is generated directly from `full/image.jpg`; smaller derivatives are never generated from larger derivatives. Images smaller than a target size are not upscaled.

The web client selects the smallest 400/800/1200/1600 derivative that satisfies the rendered image width multiplied by the browser's device pixel ratio. The viewer always remains capped at the 1600px protected derivative.

## Image processing

ImageMagick:

1. applies EXIF orientation;
2. normalizes the derivative to sRGB for browser display;
3. resizes while preserving aspect ratio and without upscaling;
4. applies restrained post-resize sharpening;
5. strips EXIF, camera, GPS, and unnecessary profile metadata;
6. writes progressive JPEG output at quality 88; and
7. applies the selected proof/watermark treatment after resizing.

DPI metadata is intentionally not set. Browser image quality is determined by pixel dimensions and device pixel ratio, not print DPI.

## One-time responsive thumbnail backfill

After deploying the responsive-thumbnail implementation, run:

```bash
bin/regenerate-responsive-thumbnails.sh
```

This checks **every album in the database**, including albums that never previously had thumbnails generated. It does not rely on the legacy `thumbsCreated` flag.

The script generates missing responsive derivatives with **no proof or watermark**. An album is skipped when every database image has the complete new structure. This makes the backfill safe to rerun after interruption.

To test or repair one album first:

```bash
bin/regenerate-responsive-thumbnails.sh <album-id>
```

The script reports processed, skipped, and failed album counts and returns a non-zero status if any album fails.

### Important protection behavior

For an album that has never had thumbnails generated, the root file is still the original. The first generation copies that file to `full/` **before** replacing the root path with the 1600px web derivative. The `full/` copy must never be generated from a derivative.

The one-time backfill intentionally removes any existing proof/watermark treatment on albums it migrates because it regenerates derivatives with `none`. After the backfill, use the normal album administration regeneration action with `proof` or `watermark` for albums that require those treatments. That regeneration reads from the preserved `full/` originals.

## Normal regeneration

The normal admin thumbnail action supports:

- `missing` — generate a responsive family when one or more required derivatives are missing.
- `all` — regenerate the entire responsive family from the preserved original.

Proof and watermark options are applied independently to every generated size.
