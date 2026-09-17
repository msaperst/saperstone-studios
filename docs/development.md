# Development Guide

This document provides a practical overview of the Saperstone Studios website development environment. The root README is intentionally kept short; implementation and operational details belong here or in other files under `docs/`.

## Application stack

The website is a PHP application packaged and deployed with Docker Compose. Current development and CI use PHP 8.4. Composer manages PHP dependencies and the test commands.

The Compose application includes the web/PHP service and SQL database. Production uses prebuilt images from GitHub Container Registry rather than building application source on the production server.

## Repository layout

Some of the important top-level areas are:

- `.docker/` - Dockerfiles and container configuration.
- `.github/workflows/` - GitHub Actions CI, security, build, and deployment workflows.
- `bin/` - repository utility and setup scripts.
- `content/` - application content used by the site and local/test environments.
- `docs/` - development and operational documentation.
- `tests/` - automated test suites.
- `composer.json` - PHP dependencies and the canonical local test commands.
- `docker-compose.yml` - local application orchestration.

## Local setup

Install PHP dependencies with Composer:

```bash
composer install
```

Create a local `.env` containing the environment-specific values required by Docker Compose and the application. Do not commit credentials or production secrets.

Build and start the local application with:

```bash
docker compose up --build
```

To start it in the background:

```bash
docker compose up --build -d
```

## Testing

The project has multiple test layers rather than a single all-in-one test command. See [Testing](testing.md) for instructions on running the suites locally and [Continuous Integration](ci.md) for the automated validation performed by GitHub Actions.

## Continuous delivery

Changes are validated through pull requests targeting `develop`. Once changes reach `develop`, GitHub-hosted runners build the PHP and SQL images for AMD64 and ARM64 and publish them to GHCR. A repository-scoped self-hosted runner on production then pulls and deploys those images.

See [Deployment](deployment.md) for the delivery flow and [Self-Hosted Production Runner](self-hosted-runner.md) for runner setup and recovery.

## Site map generation

The production PHP image pipeline runs:

```bash
python bin/create-site-map.py
```

before building and publishing the PHP image. Changes that affect generated site-map content should account for this build step.

## Retouch thumbnails

When creating new retouch thumbnails, first resize the image to a 90-pixel short side. Depending on orientation:

```bash
convert -resize x90 EmilyAfter.jpg Emily.jpg
```

or:

```bash
convert -resize 90x EmilyAfter.jpg Emily.jpg
```

Then center-crop it to 90x90:

```bash
convert Emily.jpg -gravity center -crop 90x90+0+0 +repage Emily.jpg
```

## Documentation

Keep the root `README.md` as the entry point rather than turning it into a complete operations manual. Detailed procedures should live under `docs/` and be linked from the README.

When a deployment, test, or development procedure materially changes, update the corresponding documentation in the same pull request so the documented procedure continues to match the repository behavior.
