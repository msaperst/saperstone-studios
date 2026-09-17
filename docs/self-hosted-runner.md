# Self-Hosted Production Runner

This document describes how to recreate the GitHub Actions self-hosted runner
used to deploy Saperstone Studios to the DietPi production server.

The runner is intentionally used **only for deployment**. Builds and tests run on
GitHub-hosted runners, which build the PHP and SQL Docker images and push them to
GitHub Container Registry (GHCR). The production runner then pulls those images
and applies them with Docker Compose.

## Security model

The production runner has direct access to Docker on the production server, so it
must be treated as a privileged deployment agent.

- Keep the repository private while the production self-hosted runner is attached.
- Register the runner to this repository rather than making it generally available.
- Do not give the `github-runner` user sudo access.
- The runner does require membership in the `docker` group. Docker access is
  effectively root-equivalent, so repository workflow write access should be
  treated as production access.
- GitHub does not require inbound SSH or another inbound management port. The
  runner connects outbound to GitHub.
- Production secrets remain in the production host's `.env` file rather than
  being copied into the GitHub Actions workflow.
- Only the PHP application's HTTP/HTTPS ports are published on the production
  host. MySQL remains internal to the Docker Compose network.

## 1. Create the runner account

Create a dedicated unprivileged account:

```bash
sudo useradd --create-home --shell /bin/bash github-runner
```

Verify it:

```bash
id github-runner
```

Do not configure a password or sudo access for this account.

## 2. Download the GitHub Actions runner

In GitHub, open the repository and go to:

`Settings -> Actions -> Runners -> New self-hosted runner`

Select **Linux** and **ARM64**. GitHub will provide the current download URL,
checksum, and registration command. Use the version and checksum GitHub provides
rather than copying an old runner version from this document.

Create the runner directory and download the archive as `github-runner`:

```bash
sudo -u github-runner -H bash
cd ~
mkdir -p actions-runner
cd actions-runner
```

Run the download command shown by GitHub, then verify the SHA-256 checksum using
the checksum shown on the runner setup page. For example:

```bash
echo "<EXPECTED_SHA256>  actions-runner-linux-arm64-<VERSION>.tar.gz" | sha256sum -c
```

Only continue if the checksum reports `OK`.

Extract the runner:

```bash
tar xzf actions-runner-linux-arm64-<VERSION>.tar.gz
```

The downloaded archive can be removed after the runner has been installed and
verified.

## 3. Install runner dependencies

If the runner reports missing system dependencies, do not grant the runner sudo
access. Exit back to the administrative account and run:

```bash
cd /home/github-runner/actions-runner
sudo ./bin/installdependencies.sh
```

## 4. Register the runner

Return to the runner account:

```bash
sudo -u github-runner -H bash
cd ~/actions-runner
```

Run the `./config.sh` command provided by GitHub's **New self-hosted runner** page.
The registration token is short-lived, so always obtain a new command from GitHub
when rebuilding the runner.

Use these settings when prompted:

- Runner group: `Default`
- Runner name: `DietPi`
- Work folder: `_work`

After registration, add the custom label `saperstone-production` to the runner in
GitHub. The resulting labels should include:

- `self-hosted`
- `Linux`
- `ARM64`
- `saperstone-production`

The deployment workflow should target:

```yaml
runs-on: [self-hosted, saperstone-production]
```

## 5. Install the systemd service

Return to the administrative account and install the runner as a service owned by
`github-runner`:

```bash
cd /home/github-runner/actions-runner
sudo ./svc.sh install github-runner
sudo ./svc.sh start
sudo ./svc.sh status
```

The runner should appear as **Idle** on the repository's GitHub Actions runner
page when it is connected and waiting for work.

## 6. Grant Docker access

The deployment runner needs to run Docker Compose against the production
containers:

```bash
sudo usermod -aG docker github-runner
```

Verify the Docker socket is owned by the `docker` group:

```bash
getent group docker
ls -l /var/run/docker.sock
```

Start a new login/session for `github-runner` so the new group membership takes
effect, then verify:

```bash
docker ps
```

This must work without sudo.

## 7. Grant access to the production environment file

The production Compose configuration is stored at:

```text
/home/dietpi/docker-compose.yml
/home/dietpi/.env
```

Create a group specifically for reading the deployment environment and add both
the administrative account and runner to it:

```bash
sudo groupadd saperstone-deploy
sudo usermod -aG saperstone-deploy dietpi
sudo usermod -aG saperstone-deploy github-runner
```

Protect the environment file:

```bash
sudo chown dietpi:saperstone-deploy /home/dietpi/.env
sudo chmod 640 /home/dietpi/.env
```

After starting a new runner login/session, verify the runner can read and validate
the production Compose configuration:

```bash
cd /home/dietpi
docker compose config --quiet
echo $?
```

The expected exit code is `0`.

## 8. Verify deployment access

The runner should be able to deploy from `/home/dietpi` using only:

```bash
docker compose pull
docker compose up -d
```

The deployment does not need to stop or remove the existing containers first.
Docker Compose recreates containers when their image or configuration changes.

Verify the application and database containers are running:

```bash
docker ps
```

MySQL should show its internal container ports without a host mapping such as
`0.0.0.0:<port>->3306`.

Verify PHP can reach MySQL through the Compose network:

```bash
docker exec saperstonestudios_php php -r \
    '$s=@fsockopen("mysql",3306,$e,$m,5); echo $s ? "MySQL reachable\n" : "FAILED: $e $m\n";'
```

Expected output:

```text
MySQL reachable
```

## Maintenance and troubleshooting

Check the runner service:

```bash
cd /home/github-runner/actions-runner
sudo ./svc.sh status
```

Restart it if necessary:

```bash
sudo ./svc.sh stop
sudo ./svc.sh start
```

If GitHub shows the runner as offline, check the systemd service first, then
confirm the server has outbound HTTPS connectivity.

When upgrading or rebuilding the runner, use the current ARM64 runner version and
commands supplied by GitHub rather than relying on the version originally
installed.

If deployment fails with Docker permission errors, verify that `github-runner` is
still a member of the `docker` group and start a new login/session after changing
group membership.

If Compose cannot read environment variables, verify:

```bash
ls -l /home/dietpi/.env
groups github-runner
```

The `.env` file should be owned by the `saperstone-deploy` group with mode `640`,
and `github-runner` should be a member of that group.
