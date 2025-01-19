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
Everything is orchestrated with Docker and docker-compose
### Locally
To build and deploy locally, simply run
```shell
docker-compose up --build
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
apt update
apt upgrade
apt install -y python3 python3-venv libaugeas0
python3 -m venv /opt/certbot/
/opt/certbot/bin/pip install --upgrade pip
/opt/certbot/bin/pip install certbot certbot-apache
ln -s /opt/certbot/bin/certbot /usr/bin/certbot
certbot renew --dry-run
```
If you get an error about `Error Parsing variable: ${SERVER_NAME}`, go into the apache conf files and 
update `${SERVER_NAME}` on the first 4 lines to `saperstonestudios.com` and then re-run the dry-run command. 
If all of the above works, finally run the below command:
```bash
certbot renew
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

### Running API Tests

### Running UI Tests

## Code Management
When email issue pop, it's possible that the oauth token has expire. If you need
a new `credentials.json` file, instructions are here: 
https://developers.google.com/gmail/api/quickstart/js. When updating the 
`credentials.json` file, delete the `token.json` file and then run a test from the
commandline. When it pauses, hit enter to get the correct prompts. Be sure to 
update the secrets in GHA.