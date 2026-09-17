# Deployment

Production delivery uses GitHub Actions, GitHub Container Registry (GHCR), and a pull-based deployment timer on the DietPi web server. GitHub builds the images; production decides when to pull and run a completed build.

## Delivery flow

When changes are pushed to `develop`, `.github/workflows/build-and-deploy.yml` builds the production PHP and SQL images on GitHub-hosted Ubuntu runners.

Both jobs build multi-platform images for:

- `linux/amd64`
- `linux/arm64`

The images are published to GHCR with branch and commit-SHA tags. The PHP job also regenerates the site map with `python bin/create-site-map.py` before building the image.

GitHub Actions does not deploy to production and no self-hosted GitHub Actions runner is attached to the web server.

Instead, the DietPi host checks every five minutes for the newest successful production-image workflow run on `develop`. When it sees a successful commit that has not been deployed, it runs:

```bash
docker compose pull
docker compose up -d
```

The production host does not build the application source as part of deployment.

See [Production Pull Deployment](production-pull-deployment.md) for installation, security, maintenance, and recovery of the production-side deployment timer.

## Production configuration

The production Compose configuration and environment file remain on the production server:

```text
/home/dietpi/docker-compose.yml
/home/dietpi/.env
```

Production secrets are not copied into GitHub Actions. The production server requires outbound HTTPS for GitHub/GHCR but no inbound SSH port for deployment.

## Container registry access

For a public repository, the production PHP and SQL GHCR packages should also be publicly readable so DietPi can pull them without storing a GitHub credential. Repository visibility and package visibility are separate settings in GitHub, so verify both packages after changing the repository to public.

## TLS certificates

Certificate renewal and its known troubleshooting steps are documented separately because certificate maintenance is an operational procedure rather than part of the normal application deployment pipeline.

See [TLS Certificate Maintenance](certificates.md).

## CI versus delivery

Pull request validation and security checks are documented in [Continuous Integration](ci.md). Those workflows validate changes before merge; this document covers what happens after code reaches `develop` and is delivered to production.
