# Deployment

Production delivery is handled by GitHub Actions and Docker. Build work stays on GitHub-hosted runners; the production server only pulls and runs prebuilt images.

## Delivery flow

When changes are pushed to `develop`, `.github/workflows/build-and-deploy.yml` runs the production delivery pipeline.

The PHP and SQL image jobs run on GitHub-hosted Ubuntu runners. They build multi-platform images for:

- `linux/amd64`
- `linux/arm64`

The images are published to GitHub Container Registry (GHCR) with branch and commit-SHA tags. The PHP job also regenerates the site map with `python bin/create-site-map.py` before building the image.

The deployment job waits for both image builds to succeed. It then runs on the repository-scoped self-hosted runner labeled `saperstone-production` on the DietPi production server.

Production deploys with:

```bash
docker compose pull
docker compose up -d
```

The production host does not build the application source as part of deployment.

## Production configuration

The production Compose configuration and environment file remain on the production server:

```text
/home/dietpi/docker-compose.yml
/home/dietpi/.env
```

Production secrets are not copied into the GitHub Actions workflow.

GitHub does not SSH into the production server. The self-hosted runner maintains an outbound connection to GitHub and receives the deployment job through that connection.

For installation, permissions, maintenance, and recovery of the runner itself, see [Self-Hosted Production Runner](self-hosted-runner.md).

## TLS certificates

Certificate renewal and its known troubleshooting steps are documented separately because certificate maintenance is an operational procedure rather than part of the normal application deployment pipeline.

See [TLS Certificate Maintenance](certificates.md).

## CI versus delivery

Pull request validation and security checks are documented in [Continuous Integration](ci.md). Those workflows validate changes before merge; this document covers what happens after code reaches `develop` and is delivered to production.
