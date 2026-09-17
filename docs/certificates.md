# TLS Certificate Maintenance

This document preserves the certificate renewal procedure and troubleshooting notes that were previously kept in the root README. Certificate maintenance is performed periodically and is intentionally documented separately from normal application deployment.

The TLS certificate uses Certbot/Let's Encrypt, and the certificate files themselves live on the production host.

## Existing references

The original setup used this [Let's Encrypt with Docker guide](https://phoenixnap.com/kb/letsencrypt-docker) for the basic setup and the [Certbot Apache instructions](https://certbot.eff.org/instructions?ws=apache&os=pip) for installing Certbot into the PHP container.

## Renewal procedure

Docker exec into the PHP container, then install Certbot and the Apache plugin if needed:

```bash
apt update && apt install -y certbot python3-certbot-apache
```

First perform a dry run:

```bash
certbot renew --dry-run
```

If the dry run succeeds, renew the certificate:

```bash
certbot renew
```

After renewal, restart the PHP container if necessary:

```bash
docker restart saperstonestudios_php
```

## Known troubleshooting

### SERVER_NAME parsing error

If Certbot reports:

```text
Error Parsing variable: ${SERVER_NAME}
```

go into the Apache configuration files and update `${SERVER_NAME}` on the first four lines to `saperstonestudios.com`, then rerun the dry-run command.

### python-augeas installation failure

There have been dependency installation problems with newer versions of `python-augeas`. Version 1.2 previously failed to install while version 1.1 worked. The command used to force version 1.1 was:

```bash
/opt/certbot/bin/pip install --force-reinstall -v "python-augeas==1.1"
```

After resolving the dependency issue, retry the Certbot commands above.

## Manual DNS challenge

Newer Certbot implementations have previously caused problems with this setup. The known working fallback is a manual DNS challenge:

```bash
certbot certonly --manual --preferred-challenges dns -d saperstonestudios.com
```

Follow Certbot's prompts to create the requested DNS challenge record.

After completing the challenge, it may be necessary to run the command again and select:

```text
Renew & replace the certificate (may be subject to CA rate limits)
```

Then restart the PHP container:

```bash
docker restart saperstonestudios_php
```

## Future improvement

A better long-term option may be to separate certificate management from the PHP container and run Certbot as its own networked container. The previous README kept this example as a possible direction:

```yaml
services:
  web:
    image: php:8.4-apache-bookworm
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./letsencrypt:/etc/letsencrypt

  certbot:
    image: certbot/certbot
    volumes:
      - ./letsencrypt:/etc/letsencrypt
      - ./www:/var/www/html
    command: certonly --webroot -w /var/www/html -d saperstonestudios.com --email you@example.com --agree-tos --non-interactive
```

This is a possible future architecture, not the currently documented production renewal procedure.
