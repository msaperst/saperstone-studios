# Saperstone Studios Website

Something about how awesome this is

## Development

### Retouch

When creating new thumbs for retouch:

- first create 90 (short side) thumbs:
  `convert -resize x90 EmilyAfter.jpg Emily.jpg` OR `convert -resize 90x EmilyAfter.jpg Emily.jpg`
- then crop it:
  `convert Emily.jpg -gravity center -crop 90x90+0+0 +repage Emily.jpg`

## Deploying

Everything is orchestrated with Docker and docker compose

### Locally

To build and deploy locally, simply run

```shell
docker compose up --build
```

### Pipeline

### Certificate

The ssl certificate is done with certbot for letsencrypt and the certs
themselves live on the host machine. Used this
[blog post](https://phoenixnap.com/kb/letsencrypt-docker) for basic setup,
and [this page](https://certbot.eff.org/instructions?ws=apache&os=pip) for
jamming in certbot into our php container

To update the cert, install certbot according to the above instructions, or use the below.
docker exec into the php container and then run the below commands:

```bash
apt update && apt install -y certbot python3-certbot-apache
certbot renew --dry-run
```

If you get an error about `Error Parsing variable: ${SERVER_NAME}`, go into the apache conf files and
update `${SERVER_NAME}` on the first 4 lines to `saperstonestudios.com` and then re-run the dry-run command.
If you get an error installing a dependency (when doing a `pip install`), sometimes you need to install an
older version of that dependency. This happened with `python-augeas`. Version 1.2 kept failing to install,
and manually installing version 1.1 make the rest of the commands work.
`/opt/certbot/bin/pip install --force-reinstall -v "python-augeas==1.1"`
If all of the above works, finally run the below command:

```bash
certbot renew
```

I had issues with the newer certbot implementations, and needed to do a
manual DNS challenge. This should probably be looked at, and resolved, maybe
even by setting up certbot as a separate networked container. The command that
works is:

```bash
certbot certonly --manual --preferred-challenges dns -d saperstonestudios.com
```

The better option is to split responsibility, running certbot in a separate
container. A snippet example is below:

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

## Testing

All the testing is managed by `composer`. To run tests, ensure `composer` is
installed, then run the desired commands from below

### Running Unit Tests

```shell
composer unit-test
```

This will not only run the unit tests, but also calculate the code coverage
for the unit tests. The most useful results are displayed on the commandline,
but if you want something for the record, the below reports are generated:

* junit: `reports/ut-junit.xml`
* testdox: `reports/ut-results.html`
* clover coverage: `reports/ut-coverage/index.html`

### Running Integration Tests

Before you can run the integration tests, a database must be stood up to run
against. If the application is deployed, that will work, otherwise, stand
up a database for testing with the below:

```shell
composer integration-pre-test
```

One you have a database stood up to run tests against, simply run the
integration tests

```shell
composer integration-test
```

This will not only run the integration tests, but also calculate the code
coverage for the integration tests. The most useful results are displayed
on the commandline, but if you want something for the record, the below
reports are generated:

* junit: `reports/it-junit.xml`
* testdox: `reports/it-results.html`
* clover coverage: `reports/it-coverage/index.html`

### Running Code Coverage

TODO

### Running API Tests

Launch the app

```bash
docker compose up --build -d
```

Setup local environment variables

```bash
set -a
source .env
set +a
export DB_HOST=localhost
```

Ensure the api tools are built

```bash
composer clean
composer install --prefer-dist --no-progress --no-suggest
```

Ensure the app is up

```bash
curl --retry 50 -f --retry-all-errors --retry-delay 5  -s -o /dev/null "http://localhost:90/"
```

Run the tests

```bash
COMPOSER_PROCESS_TIMEOUT=1200 composer api-test
```

### Running UI Tests

install chromedriver

```bash
sudo apt install chromium-chromedriver
```

run chromedriver

```bash
chromedriver --port=4444
```

run your tests

```bash
TBD
```

## Code Management

When email issue pop, it's possible that the oauth token has expire. If you need
a new `credentials.json` file, instructions are here:
https://developers.google.com/gmail/api/quickstart/js. When updating the
`credentials.json` file, delete the `token.json` file and then run a test from the
commandline. When it pauses, hit enter to get the correct prompts. Be sure to
update the secrets in GHA.
