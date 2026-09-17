# Saperstone Studios Website

Website and supporting tooling for Saperstone Studios.

## Development

The application is built with PHP 8.4 and Docker Compose, with Composer managing PHP dependencies and test commands.

To build and start the application locally:

```bash
docker compose up --build
```

For repository layout, local setup, retouch thumbnail generation, and other development details, see the [Development Guide](docs/development.md).

## Testing and CI

The project uses separate unit, integration, API, UI, Behat, dependency, static-analysis, and container/security workflows. Pull requests to `develop` run the applicable validation workflows before changes are merged.

For the complete CI/test overview, local commands, reports, and workflow behavior, see [CI and Testing](docs/ci-testing.md).

## Deployment

Production deployment is handled by GitHub Actions. On a push to `develop`, GitHub-hosted runners build the PHP and SQL images for AMD64 and ARM64 and publish them to GitHub Container Registry (GHCR). After both images are available, the deployment job runs on the repository-scoped `saperstone-production` self-hosted runner on the DietPi production server.

The production runner deploys the prebuilt images with:

```bash
docker compose pull
docker compose up -d
```

GitHub does not SSH into production; the self-hosted runner maintains an outbound connection to GitHub. Production secrets and Compose configuration remain on the production host.

For installation, security, verification, and recovery instructions, see [Self-Hosted Production Runner](docs/self-hosted-runner.md).

## Documentation

- [Development Guide](docs/development.md) - local development, repository structure, and developer notes.
- [CI and Testing](docs/ci-testing.md) - automated test suites, security checks, reports, and build/deploy flow.
- [Self-Hosted Production Runner](docs/self-hosted-runner.md) - production runner setup, permissions, maintenance, and recovery.
