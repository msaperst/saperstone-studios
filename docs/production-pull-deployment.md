# Production Pull Deployment

Production deployment is initiated by the DietPi web server rather than by GitHub Actions. This keeps the production host from being registered as a GitHub self-hosted runner and allows the repository to remain public safely.

## Security model

GitHub Actions builds the production images and publishes them to GitHub Container Registry (GHCR), but GitHub cannot execute commands on the production server. The DietPi host periodically checks GitHub for the latest successful production-image workflow run and pulls the images itself.

The production server requires outbound HTTPS only. No inbound SSH port is required for deployment.

The GHCR `php` and `sql` packages must be publicly readable for anonymous `docker compose pull`. If they remain private, production must authenticate to GHCR with a read-only package credential instead.

## Files

The repository contains the deployment components under `ops/`:

```text
ops/deploy-production.sh
ops/systemd/saperstone-deploy.service
ops/systemd/saperstone-deploy.timer
```

Production continues to keep its Compose configuration and environment outside the repository:

```text
/home/dietpi/docker-compose.yml
/home/dietpi/.env
```

## How deployment works

Every five minutes, `saperstone-deploy.timer` starts the oneshot deployment service. The script queries GitHub for the newest successful `build-and-deploy.yml` run on `develop`.

If that commit SHA is already recorded as deployed, the script exits without touching the containers. If it is new, the script runs:

```bash
cd /home/dietpi
docker compose pull
docker compose up -d
```

Only after both commands succeed does it record the deployed SHA under:

```text
/home/dietpi/.local/state/saperstone-deploy/last-deployed-sha
```

Checking the workflow result before pulling is important: the PHP and SQL images build independently, so production should not deploy while one image build is still in progress.

## Install on DietPi

After this change is merged, copy the deployment script and systemd units from the repository to the production server:

```bash
sudo install -m 755 ops/deploy-production.sh /usr/local/sbin/saperstone-deploy
sudo install -m 644 ops/systemd/saperstone-deploy.service /etc/systemd/system/saperstone-deploy.service
sudo install -m 644 ops/systemd/saperstone-deploy.timer /etc/systemd/system/saperstone-deploy.timer
sudo systemctl daemon-reload
sudo systemctl enable --now saperstone-deploy.timer
```

The commands above assume the repository files have been copied or checked out temporarily on the server for installation. The running deployment does not require a production source checkout.

Verify the timer:

```bash
systemctl status saperstone-deploy.timer
systemctl list-timers saperstone-deploy.timer
```

Run one deployment check immediately:

```bash
sudo systemctl start saperstone-deploy.service
sudo journalctl -u saperstone-deploy.service -n 50 --no-pager
```

## Remove the old GitHub runner

Once pull-based deployment has been verified, remove the `DietPi` self-hosted runner from the repository in GitHub and stop/remove its systemd service on the server. The old `github-runner` account and deployment-specific group can then be removed after confirming nothing else uses them.

Do not make the repository public while the privileged production self-hosted runner remains attached.

## Troubleshooting

Check recent deployment attempts with:

```bash
sudo journalctl -u saperstone-deploy.service --since today
```

If GitHub cannot be queried, verify outbound HTTPS and that `curl` and `python3` are installed. The public GitHub API is used without credentials, so the host is subject to GitHub's unauthenticated API rate limit; a five-minute polling interval stays well below normal limits for this single request.

If image pulls fail with an authorization error, verify the GHCR package visibility or Docker registry authentication. If Compose fails, run the same commands manually from `/home/dietpi` and inspect the container logs before changing the deployment state file.
