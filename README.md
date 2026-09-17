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

Automated validation includes unit, integration, API, UI, Behat, dependency, static-analysis, and security checks.

See [Testing](docs/testing.md) for running tests locally and [Continuous Integration](docs/ci.md) for the GitHub Actions validation workflows.

## Deployment

On a push to `develop`, GitHub-hosted runners build and publish the production Docker images. The DietPi production server independently checks for successful builds and pulls/deploys new images.

See [Deployment](docs/deployment.md) for the delivery flow and [Production Pull Deployment](docs/production-pull-deployment.md) for production-side setup, security, maintenance, and recovery.

## Certificate maintenance

TLS certificate renewal and the known Certbot troubleshooting procedures are documented in [TLS Certificate Maintenance](docs/certificates.md).

## Documentation

- [Development Guide](docs/development.md) - local development, repository structure, and developer notes.
- [Testing](docs/testing.md) - running the automated test suites locally.
- [Continuous Integration](docs/ci.md) - GitHub Actions validation, security checks, reports, and artifacts.
- [Deployment](docs/deployment.md) - production image build and continuous-delivery flow.
- [Production Pull Deployment](docs/production-pull-deployment.md) - production polling, deployment timer, security, and recovery.
- [TLS Certificate Maintenance](docs/certificates.md) - recurring certificate renewal and troubleshooting.
