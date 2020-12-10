def branch
def version
def dockerRepo = "victor:9086"
def dockerRegistry = "${dockerRepo}/saperstone-studios"

node() {
    ansiColor('xterm') {
        branch = env.BRANCH_NAME.replaceAll(/\//, "-")
        version = "$branch-${env.BUILD_NUMBER}"
        stage('Checkout Code') { // for display purposes
            cleanWs()
            checkout scm
        }
        stage('Run Unit Tests') {
            try {
                sh "composer validate"
                sh "composer install --prefer-dist --no-progress --no-suggest"
                sh "composer unit-test"
            } finally {
                junit 'reports/ut-junit.xml'
                publishHTML([
                        allowMissing         : false,
                        alwaysLinkToLastBuild: true,
                        keepAll              : true,
                        reportDir            : 'reports/',
                        reportFiles          : 'ut-results.html',
                        reportName           : 'Unit Test Results Report'
                ])
                publishHTML([
                        allowMissing         : false,
                        alwaysLinkToLastBuild: true,
                        keepAll              : true,
                        reportDir            : 'reports/ut-coverage',
                        reportFiles          : 'index.html',
                        reportName           : 'Unit Test Coverage Report'
                ])
            }
        }
        withCredentials([
                usernamePassword(
                        credentialsId: 'saperstone-studios-contact',
                        usernameVariable: 'emailUser',
                        passwordVariable: 'emailPass'
                ),
                usernamePassword(
                        credentialsId: 'saperstone-studios-gmail',
                        usernameVariable: 'emailUserX',
                        passwordVariable: 'emailPassX'
                ),
                usernamePassword(
                        credentialsId: 'paypal',
                        usernameVariable: 'paypalUser',
                        passwordVariable: 'paypalPass'
                ),
                usernamePassword(
                        credentialsId: 'docker-sql-root',
                        usernameVariable: 'sqlRootUser',
                        passwordVariable: 'sqlRootPass'
                ),
                usernamePassword(
                        credentialsId: 'docker-sql-root',
                        usernameVariable: 'sqlRootUser',
                        passwordVariable: 'sqlRootPass'
                ),
                usernamePassword(
                        credentialsId: 'docker-sql-user',
                        usernameVariable: 'sqlUser',
                        passwordVariable: 'sqlPass'
                ),
                string(
                        credentialsId: 'paypal-signature',
                        variable: 'paypalSignature'
                ),
                string(
                        credentialsId: 'twitter-consumer-key',
                        variable: 'twitterConsumerKey'
                ),
                string(
                        credentialsId: 'twitter-consumer-secret',
                        variable: 'twitterConsumerSecret'
                ),
                string(
                        credentialsId: 'twitter-token',
                        variable: 'twitterToken'
                ),
                string(
                        credentialsId: 'twitter-token-secret',
                        variable: 'twitterTokenSecret'
                )
        ]) {
            stage('Setup env File') {
                sh "echo '# tool hosting information\n\
ADMIN_PORT=9090\n\
HTTP_PORT=90\n\
HTTPS_PORT=9443\n\
SERVER_NAME=victor\n\
\n\
# database information\n\
DB_ROOT=${sqlRootPass}\n\
DB_PORT=3406\n\
DB_NAME=saperstone-studios\n\
DB_USER=${sqlUser}\n\
DB_PASS=${sqlPass}\n\
\n\
# email information\n\
EMAIL_HOST=ssl://smtp.gmail.com\n\
EMAIL_PORT=465\n\
EMAIL_USER=${emailUser}\n\
EMAIL_PASS=${emailPass}\n\
EMAIL_USER_X=${emailUserX}\n\
EMAIL_PASS_X=${emailPassX}\n\
\n\
# twitter information\n\
CONSUMER_KEY=${twitterConsumerKey}\n\
CONSUMER_SECRET=${twitterConsumerSecret}\n\
TOKEN=${twitterToken}\n\
TOKEN_SECRET=${twitterTokenSecret}\n\
\n\
# paypal information\n\
PAYPAL_USERNAME=${paypalUser}\n\
PAYPAL_PASSWORD=${paypalPass}\n\
PAYPAL_SIGNATURE=${paypalSignature}' > .env"
            }
        }
        stage('Run Integration Tests') {
            try {
                withCredentials([
                        usernamePassword(
                                credentialsId: 'docker-hub',
                                usernameVariable: 'dockerUser',
                                passwordVariable: 'dockerPass'
                        )
                ]) {
                    sh "docker login -u ${dockerUser} -p ${dockerPass}"
                }
                sh "composer integration-pre-test"
                sh "composer integration-test"
            } catch (Exception e) {
                if( fileContains( 'reports/it-junit.xml', 'testsuite name=\\"tests/coverage/integration/\\".* errors=\\"1\\" failures=\\"0\\" skipped=\\"0\\"') &&
                     fileContains( 'reports/it-junit.xml', 'Exception: Request error for API call: Resolving timed out') ) {
                     echo 'Experiencing a twitter timeout issue, this is "expected", but unfortunate'
                 } else {
                    throw e
                 }
            } finally {
                sh "composer integration-post-test"
                sh "docker logout"
                junit 'reports/it-junit.xml'
                publishHTML([
                        allowMissing         : false,
                        alwaysLinkToLastBuild: true,
                        keepAll              : true,
                        reportDir            : 'reports/',
                        reportFiles          : 'it-results.html',
                        reportName           : 'Integration Test Results Report'
                ])
                publishHTML([
                        allowMissing         : false,
                        alwaysLinkToLastBuild: true,
                        keepAll              : true,
                        reportDir            : 'reports/it-coverage',
                        reportFiles          : 'index.html',
                        reportName           : 'Integration Test Coverage Report'
                ])
            }
        }
        stage('Run Sonar Analysis') {
            sh """sonar-scanner \
                -Dsonar.projectKey=saperstone-studios \
                -Dsonar.projectName='Saperstone Studios' \
                -Dsonar.projectVersion=2.0 \
                -Dsonar.branch=${branch} \
                -Dsonar.sources=./bin,./public,./src,./templates \
                -Dsonar.tests=./tests \
                -Dsonar.exclusions=public/js/jqBootstrapValidation.js \
                -Dsonar.php.tests.reportPath=./reports/junit.xml \
                -Dsonar.php.coverage.reportPaths=./reports/it-clover.xml,./reports/ut-clover.xml"""
        }
        stage('Prep Files') {
            parallel(
                    "Compress JS": {
                        compress('js')
                    },
                    "Compress CSS": {
                        compress('css')
                    }
            )
        }
        stage('Setup Files') {
            try {
                sh "rm -r content"
            } catch (e) {
            }
            sh "ln -s /home/msaperst/saperstone-studios/content content"
            try {
                sh "rm -r logs"
            } catch (e) {
            }
            sh "ln -s /home/msaperst/saperstone-studios/logs logs"
        }
        stage('Kill Any Old Docker Containers') {
            parallel(
                    "PHP": {
                        killContainer("saperstonestudios_php")
                    },
                    "PHP MyAdmin": {
                        killContainer("saperstonestudios_php-myadmin")
                    },
                    "MySQL": {
                        killContainer("saperstonestudios_mysql")
                    }
            )
        }
        stage('Launch Docker Container') {
            withCredentials([
                    usernamePassword(
                            credentialsId: 'docker-hub',
                            usernameVariable: 'dockerUser',
                            passwordVariable: 'dockerPass'
                    )
            ]) {
                sh "docker login -u ${dockerUser} -p ${dockerPass}"
            }
            sh "docker-compose up --build -d"
            sh "docker logout"
        }
        stage('Clean Up') {
            sh "composer clean"
        }
        stage('API Tests') {
            parallel(
                    "Coverage Tests": {
                        stage('Run Coverage Tests') {
                            try {
                                timeout(60) {
                                    waitUntil {
                                        script {
                                            def r = sh returnStdout: true, script: 'curl -I http://localhost:90/ 2>/dev/null | head -n 1 | cut -d " " -f2'
                                            return (r.trim() == '200');
                                        }
                                    }
                                }
                                sh "COMPOSER_PROCESS_TIMEOUT=600 composer coverage-test"
                            } catch (Exception e) {
                                if( fileContains( 'reports/cov-junit.xml', 'testsuite name=\\"tests/coverage/\\".* errors=\\"1\\" failures=\\"0\\" skipped=\\"0\\"') &&
                                     fileContains( 'reports/cov-junit.xml', 'Exception: Request error for API call: Resolving timed out') ) {
                                     echo 'Experiencing a twitter timeout issue, this is "expected", but unfortunate'
                                 } else {
                                    throw e
                                 }
                            } finally {
                                junit 'reports/cov-junit.xml'
                                publishHTML([
                                        allowMissing         : false,
                                        alwaysLinkToLastBuild: true,
                                        keepAll              : true,
                                        reportDir            : 'reports/',
                                        reportFiles          : 'cov-results.html',
                                        reportName           : 'Coverage Test Results Report'
                                ])
                                step([
                                    $class: 'CloverPublisher',
                                    cloverReportDir: 'reports/',
                                    cloverReportFileName: 'cov-clover.xml'
                                ])
                                publishHTML([
                                        allowMissing         : false,
                                        alwaysLinkToLastBuild: true,
                                        keepAll              : true,
                                        reportDir            : 'reports/cov-coverage',
                                        reportFiles          : 'index.html',
                                        reportName           : 'Coverage Test Coverage Report'
                                ])
                            }
                        }
                    },
                    "API Tests": {
                        stage('Run API Tests') {
                            try {
                                timeout(60) {
                                    waitUntil {
                                        script {
                                            def r = sh returnStdout: true, script: 'curl -I http://localhost:90/ 2>/dev/null | head -n 1 | cut -d " " -f2'
                                            return (r.trim() == '200');
                                        }
                                    }
                                }
                                sh "COMPOSER_PROCESS_TIMEOUT=1200 composer api-test"
                            } finally {
                                junit 'reports/api-junit.xml'
                                publishHTML([
                                        allowMissing         : false,
                                        alwaysLinkToLastBuild: true,
                                        keepAll              : true,
                                        reportDir            : 'reports/',
                                        reportFiles          : 'api-results.html',
                                        reportName           : 'API Test Results Report'
                                ])
                            }
                        }
                    }
            )
        }
        stage('Run Chrome Page Tests') {
            try {
                withCredentials([
                        usernamePassword(
                                credentialsId: 'docker-hub',
                                usernameVariable: 'dockerUser',
                                passwordVariable: 'dockerPass'
                        )
                ]) {
                    sh "docker login -u ${dockerUser} -p ${dockerPass}"
                }
                sh 'export BROWSER=chrome; composer ui-pre-test;'
                sh '''while ! curl -sSL "http://localhost:4444/wd/hub/status" 2>&1 \
                              | jq -r '.value.ready' 2>&1 | grep "true" >/dev/null; do
                          echo 'Waiting for the Grid'
                          sleep 1
                      done'''   // from https://github.com/SeleniumHQ/docker-selenium#using-a-bash-script-to-wait-for-the-grid
                sh 'export BROWSER=chrome; COMPOSER_PROCESS_TIMEOUT=1200 composer ui-page-test;'
            } finally {
                sh 'export BROWSER=chrome; composer ui-post-test;'
                sh 'docker logout'
                junit 'reports/ui/junit.xml'
                publishHTML([
                        allowMissing         : false,
                        alwaysLinkToLastBuild: true,
                        keepAll              : true,
                        reportDir            : 'reports/ui/',
                        reportFiles          : 'results.html',
                        reportName           : 'Chrome Page Test Results Simple Report'
                ])
                publishHTML([
                        allowMissing         : false,
                        alwaysLinkToLastBuild: true,
                        keepAll              : true,
                        reportDir            : 'reports/ui/',
                        reportFiles          : 'index.html',
                        reportName           : 'Chrome Page Test Results Screenshot Report'
                ])
            }
        }
        parallel(
            'Execute Chrome BDD Tests': {
                try {
                    stage('Start ZAP') {
                        startZap(
                                host: "localhost",
                                port: 9092,
                                zapHome: "/opt/zap"
                        )
                    }
                    stage('Run Chrome BDD Tests') {
                        try {
                            withCredentials([
                                    usernamePassword(
                                            credentialsId: 'docker-hub',
                                            usernameVariable: 'dockerUser',
                                            passwordVariable: 'dockerPass'
                                    )
                            ]) {
                                sh "docker login -u ${dockerUser} -p ${dockerPass}"
                            }
                            sh 'export BROWSER=chrome; composer ui-pre-test;'
                            sh 'composer dump-autoload'
                            sh '''while ! curl -sSL "http://localhost:4444/wd/hub/status" 2>&1 \
                                          | jq -r '.value.ready' 2>&1 | grep "true" >/dev/null; do
                                      echo 'Waiting for the Grid'
                                      sleep 1
                                  done'''   // from https://github.com/SeleniumHQ/docker-selenium#using-a-bash-script-to-wait-for-the-grid
                            sh 'export BROWSER=chrome; export PROXY=http://127.0.0.1:9092; COMPOSER_PROCESS_TIMEOUT=2400 composer ui-behat-test;'
                        } finally {
                            sh 'export BROWSER=chrome; composer ui-post-test;'
                            sh 'docker logout'
                            junit 'reports/behat/default.xml'
                            publishHTML([
                                    allowMissing         : false,
                                    alwaysLinkToLastBuild: true,
                                    keepAll              : true,
                                    reportDir            : 'reports/behat/',
                                    reportFiles          : 'index.html',
                                    reportName           : 'Chrome Behat Test Results Report'
                            ])
                            publishHTML([
                                    allowMissing         : false,
                                    alwaysLinkToLastBuild: true,
                                    keepAll              : true,
                                    reportDir            : 'reports/behat/',
                                    reportFiles          : 'screenshots.html',
                                    reportName           : 'Chrome Behat Test Results Screenshot Report'
                            ])
                        }
                    }
                } finally {
                    stage('Get ZAP Results') {
                        sh 'mkdir -p results/zap'
                        sh 'wget -q -O results/zap/report.html http://localhost:9092/OTHER/core/other/htmlreport'
                        sh 'wget -q -O results/zap/report.xml http://localhost:9092/OTHER/core/other/xmlreport'
                        publishHTML([
                                allowMissing         : false,
                                alwaysLinkToLastBuild: true,
                                keepAll              : true,
                                reportDir            : 'results/zap',
                                reportFiles          : 'report.html',
                                reportName           : 'ZAP Report'
                        ])
                        archiveZap()
                    }
                }
            },
            'Monitor Network Traffic': {
                stage('Zap Monitoring') {
                    echo 'Monitoring traffic'
                }
            }
        )
        stage('Publish Containers') {
            withCredentials([
                    usernamePassword(
                            credentialsId: 'nexus-docker',
                            usernameVariable: 'dockerUser',
                            passwordVariable: 'dockerPass'
                    )
            ]) {
                sh "docker login ${dockerRepo} -u ${dockerUser} -p ${dockerPass}"
            }
            // tag and push each of our containers
            parallel(
                    "PHP": {
                        pushContainer(dockerRegistry, version, "workspace_php", "php")
                    },
                    "PHP MyAdmin": {
                        pushContainer(dockerRegistry, version, "phpmyadmin/phpmyadmin", "php-myadmin")
                    },
                    "MySQL": {
                        pushContainer(dockerRegistry, version, "workspace_mysql", "mysql")
                    }
            )
            sh "docker system prune -a -f"
            sh "docker logout ${dockerRepo}"
        }
        if( branch == 'master' ) {
            stage('Deploy to Production') {
                timeout(time: 30, unit: 'MINUTES') {
                    input(
                        message: 'Deploy To Production?',
                        ok: 'Yes',
                        parameters: [
                            booleanParam(
                                defaultValue: true,
                                description: 'Should we deploy this out to production',
                                name: 'Deploy?'
                            )
                        ]
                    )
                }
            }
            stage('Copy Container to Walter') {
                parallel(
                        "PHP": {
                            copyContainer(dockerRegistry, version,"php")
                        },
                        "PHP MyAdmin": {
                            copyContainer(dockerRegistry, version,"php-myadmin")
                        },
                        "MySQL": {
                            copyContainer(dockerRegistry, version,"mysql")
                        }
                )
            }
            stage('Stand Up New Instance') {
                sh "scp docker-compose-prod.yml 192.168.1.2:/var/www/ss-docker/"
                sh "ssh 192.168.1.2 \"sed -i 's/latest/${version}/g'\" /var/www/ss-docker/docker-compose-prod.yml"
                sh "ssh 192.168.1.2 'cd /var/www/ss-docker/; docker-compose -f docker-compose-prod.yml stop'"
                sh "ssh 192.168.1.2 'cd /var/www/ss-docker/; docker-compose -f docker-compose-prod.yml up -d'"
                sh "ssh 192.168.1.2 'docker system prune -a -f'"
            }
        }
    }
}

def compress(filetype) {
    stage("Compress " + filetype.toUpperCase()) {
        Random rnd = new Random()
        def random = rnd.nextInt(9999999)
        def output = sh returnStdout: true, script: "ls ./public/$filetype/"
        def files = output.split()
        files.each { file ->
            if (file == "mpdf.css") {
                return
            }
            //get the new filename
            def newFile = file.take(file.lastIndexOf('.')) + ".min.$filetype"
            //compress the file
            sh "uglify$filetype ./public/$filetype/$file > ./public/$filetype/$newFile"
            //remove the old file
            sh "rm ./public/$filetype/$file"
            //fix all references to old file
            sh "find ./ -type f -exec sed -i 's/$file/$newFile?$random/g' {} \\;"
        }
    }
}

def killContainer(containerName) {
    stage("Kill Container " + containerName) {
        try {
            sh "docker kill ${containerName}"
        } catch (e) {
        }
    }
}

def pushContainer(dockerRegistry, version, localContainerName, nexusContainerName) {
    stage("Pushing Container " + nexusContainerName) {
        sh "docker tag ${localContainerName} ${dockerRegistry}/${nexusContainerName}:${version}"
        sh "docker push ${dockerRegistry}/${nexusContainerName}:${version}"
        sh "docker tag ${localContainerName} ${dockerRegistry}/${nexusContainerName}:latest"
        sh "docker push ${dockerRegistry}/${nexusContainerName}:latest"
    }
}

def copyContainer(dockerRegistry, version, containerName) {
    stage("Copying Container " + containerName) {
        sh "docker save ${dockerRegistry}/${containerName}:${version} | gzip | ssh 192.168.1.2 'gunzip | docker load'"
    }
}

def fileContains(file, content) {
    try {
        sh "grep \"$content\" $file"
    } catch(Exception e) {
        return false
    }
    return true
}