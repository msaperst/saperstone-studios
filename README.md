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

### Jenkins


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

