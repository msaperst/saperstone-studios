# Runtime security and production image verification

The website uses Apache/mod_php and MySQL containers. PHP interpreter CVEs are not identified by PHPUnit, Composer Audit, SonarCloud, or CodeQL alone.

## Automated checks

- **PR container scanning** (`.github/workflows/container-scan.yml`) builds proposed Dockerfiles and blocks actionable fixable vulnerabilities. It does *not* inspect deployed images.
- **Published image security watch** (`.github/workflows/runtime-security-watch.yml`) runs daily and manually. It pulls and scans both `ghcr.io/msaperst/saperstone-studios/{php,sql}:develop` images. It compares the published PHP version against the latest official PHP 8.4 release at `https://www.php.net/releases/?json&version=8.4`.
- The watch compares each scan to its most recent completed snapshot artifact. It flags **new or escalated high/critical CVEs even if no fix exists**. Existing reviewed false positives are sourced from `.github/container-scan-allowlist.json`. The first scan creates a **baseline review** issue rather than silently accepting existing findings.
- Alerts fail the scheduled job and create/update the issue `Published image security watch requires review`; changed findings notify the issue's watchers via a comment. Do not close without reviewing the vulnerability and deployment.
- **Dependabot** (`.github/dependabot.yml`) checks the pinned PHP Docker base daily for patch-version updates and proposes PRs against the default `develop` branch. These remain approval-based and must pass the normal CI before merging.

The watch depends on upstream PHP release metadata and the vulnerability scanner's database. It cannot prove a newly disclosed, unindexed vulnerability exists unless a new upstream PHP release or scanner finding appears. It **does not auto-deploy**.

## Verify production on DietPi

The scheduled scan proves the **published GHCR images** were checked, not the containers running on the Raspberry Pi. The production host has no connected GitHub runner/deployment agent.

After the patched PR merges and GHCR publishes it, run from the production Compose directory on the Pi:

```sh
docker compose -f docker-compose.prod.yml pull php
docker compose -f docker-compose.prod.yml up -d --no-deps php
docker exec saperstonestudios_php php -v
sh bin/check-production-php-image.sh
```

The checker pulls the current GHCR tag, compares its image ID with the running PHP container's immutable image ID, and fails on a mismatch. It **does not restart containers** or alter production data. Copy the script to the Pi or run it from a checked-out repository there. The checker needs Docker access and registry access.

**Remaining gap:** automatically proving the *deployed* image digest requires a properly authenticated agent on the Pi or a trusted deployment receipt; neither has been configured. A green GHCR scan does not mean production has been redeployed. Treat a failed published-image watch as an actionable security review.
