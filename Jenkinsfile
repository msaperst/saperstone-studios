def branch
def dockerRepo = "victor:9086"
def dockerRegistry = "${dockerRepo}/saperstone-studios"

/*
Logic for the pipeline
feature branch
    start through sonar
pr
    start through sonar
    build containers and push to nexus
develop
    start w/ deploy to victor (qa)
    run through all tests
    retag in nexus
release
    deploy to walter (prod)
*/

node() {
    ansiColor('xterm') {
        branch = env.BRANCH_NAME.replaceAll(/\//, "-")

        //our dev workflow - kicked off for feature branches and PRs
        if( !'develop'.equals(branch) && !'release'.equals(branch) ) {
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
            setupConfigurationFiles()
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
                withCredentials([
                    usernamePassword(
                            credentialsId: 'GitHub_API	',
                            usernameVariable: 'gitHubUser',
                            passwordVariable: 'gitHubPass'
                    ),string(
                            credentialsId: '695f143e-326d-4f85-9959-20f9ef269cdd',
                            variable: 'sonarToken'
                    )
                ]) {
                    def sonarExtras = '';
                    if( env.CHANGE_ID ) {
                        sonarExtras = "-Dsonar.analysis.mode=preview \
    -Dsonar.github.repository=msaperst/saperstone-studios \
    -Dsonar.github.pullRequest=${env.CHANGE_ID} \
    -Dsonar.github.oauth=${gitHubPass} \
    -Dsonar.host.url=http://192.168.3.13/sonar/ \
    -Dsonar.login=${sonarToken}";
                    }
                    sh """sonar-scanner ${sonarExtras} \
                        -Dsonar.projectKey=saperstone-studios \
                        -Dsonar.projectName='Saperstone Studios' \
                        -Dsonar.projectVersion=3.0 \
                        -Dsonar.branch=${branch} \
                        -Dsonar.sources=./bin,./public,./src,./templates \
                        -Dsonar.tests=./tests \
                        -Dsonar.exclusions=public/js/jqBootstrapValidation.js,public/favicon.ico,public/img/**,public/retouch/**,public/portrait/what-to-wear/**, \
                        -Dsonar.php.tests.reportPath=./reports/junit.xml \
                        -Dsonar.php.coverage.reportPaths=./reports/it-clover.xml,./reports/ut-clover.xml"""
                }
            }
            //if feature branch, good to exit here
            if( !env.CHANGE_ID ) {
                currentBuild.result = 'SUCCESS'
                return
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
            stage('Build Containers') {
                withCredentials([
                        usernamePassword(
                                credentialsId: 'docker-hub',
                                usernameVariable: 'dockerUser',
                                passwordVariable: 'dockerPass'
                        )
                ]) {
                    sh "docker login -u ${dockerUser} -p ${dockerPass}"
                }
                sh "docker pull phpmyadmin/phpmyadmin"
                sh "docker-compose build"
                sh "docker logout"
            }
            stage('Publish Containers') {
                // tag and push each of our containers
                parallel(
                        "PHP": {
                            pushContainer(dockerRegistry, ['latest','ci',branch], "workspace_php", "php")
                        },
                        "PHP MyAdmin": {
                            pushContainer(dockerRegistry, ['latest','ci',branch], "phpmyadmin/phpmyadmin", "php-myadmin")
                        },
                        "MySQL": {
                            pushContainer(dockerRegistry, ['latest','ci',branch], "workspace_mysql", "mysql")
                        }
                )
            }
            stage('Clean Up') {
                sh "docker system prune -a -f"
            }
            currentBuild.result = 'SUCCESS'
            return
        }

        //our develop workflow - kicked off when code is merged into develop
        if( 'develop'.equals(branch) ) {
            stage('Checkout Configuration') { // for display purposes
                sh "docker system prune -a -f"
                cleanWs()
                checkout scm
            }
            stage('Pull Containers') {
                parallel(
                        "PHP": {
                            pullContainer(dockerRegistry, 'ci', "php")
                        },
                        "PHP MyAdmin": {
                            pullContainer(dockerRegistry, 'ci', "php-myadmin")
                        },
                        "MySQL": {
                            pullContainer(dockerRegistry, 'ci', "mysql")
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
            setupConfigurationFiles()
            stage('Launch New Application') {
                sh "mv docker-compose-prod.yml docker-compose.yml"
                sh "sed -i 's/prod/ci/g' docker-compose.yml"
                sh "docker-compose down"
                sh "docker-compose up -d"
            }
            stage('Clean Up') {
                sh "composer clean"
                sh "composer install --prefer-dist --no-progress --no-suggest"
            }
            stage('BackEnd Tests') {
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
                                    sh "composer coverage-pre-test"
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
                    launchBrowserDocker('chrome')
                    sh 'export BROWSER=chrome; COMPOSER_PROCESS_TIMEOUT=1800 composer ui-page-test;'
                } finally {
                    closeBrowserDocker('chrome')
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
            stage('Run Chrome Functional Tests') {
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
                                        sh 'composer dump-autoload'
                                        launchBrowserDocker('chrome')
                                        sh 'export BROWSER=chrome; export PROXY=http://127.0.0.1:9092; COMPOSER_PROCESS_TIMEOUT=2400 composer ui-behat-test;'
                                    } catch (Exception e) {
                                        //TODO - not doing anything yet...but we'll need to
                                    } finally {
                                        closeBrowserDocker('chrome')
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
            }
            stage('Publish Containers') {
                // retag and push each of our containers
                parallel(
                        "PHP": {
                            pushContainer(dockerRegistry, ['qa'], "workspace_php", "php")
                        },
                        "PHP MyAdmin": {
                            pushContainer(dockerRegistry, ['qa'], "phpmyadmin/phpmyadmin", "php-myadmin")
                        },
                        "MySQL": {
                            pushContainer(dockerRegistry, ['qa'], "workspace_mysql", "mysql")
                        }
                )
            }
            stage('Clean Up') {
                sh "docker system prune -a -f"
            }
            currentBuild.result = 'SUCCESS'
            return
        }

        //our release workflow - kicked off when code is merged into release
        if( 'release'.equals(branch) ) {
            stage('Checkout Configuration') { // for display purposes
                sh "docker system prune -a -f"
                cleanWs()
                checkout scm
            }
            stage('Copy Container to Walter') {
                parallel(
                        "PHP": {
                            pullContainer(dockerRegistry, 'qa', "php")
                            pushContainer(dockerRegistry, ['prod'], "workspace_mysql", "mysql")
                            copyContainer(dockerRegistry, 'prod',"php")
                        },
                        "PHP MyAdmin": {
                            pullContainer(dockerRegistry, 'qa', "php-myadmin")
                            pushContainer(dockerRegistry, ['prod'], "workspace_mysql", "mysql")
                            copyContainer(dockerRegistry, 'prod',"php-myadmin")
                        },
                        "MySQL": {
                            pullContainer(dockerRegistry, 'qa', "mysql")
                            pushContainer(dockerRegistry, ['prod'], "workspace_mysql", "mysql")
                            copyContainer(dockerRegistry, 'prod',"mysql")
                        }
                )
                sh "docker system prune -a -f"
            }
            stage('Stand Up New Instance') {
                sh "scp docker-compose-prod.yml 192.168.1.2:/var/www/ss-docker/"
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
            //check the file for size of non-zero
            File file = new File("./public/$filetype/$newFile")
            if( file.length() == 0 ) {
                throw new Exception("$newFile has filesize 0. We have an issue.")
            }
            //remove the old file
            sh "rm ./public/$filetype/$file"
            //fix all references to old file
            sh "find ./public ./templates -type f -exec sed -i 's/$file/$newFile?$random/g' {} \\;"
        }
    }
}

def pushContainer(dockerRegistry, versions, localContainerName, nexusContainerName) {
    stage("Pushing Container " + nexusContainerName) {
        for( String version in versions) {
            sh "docker tag ${localContainerName} ${dockerRegistry}/${nexusContainerName}:${version}"
            sh "docker push ${dockerRegistry}/${nexusContainerName}:${version}"
        }
    }
}

def pullContainer(dockerRegistry, version, nexusContainerName) {
    stage("Pulling Container ${nexusContainerName}:${version}") {
        sh "docker pull ${dockerRegistry}/${nexusContainerName}:${version}"
    }
}

def setupConfigurationFiles() {
    stage('Setup configuration files') {
        parallel(
                "env File": {
                    stage('Setup env File') {
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
EMAIL_CONTACT=msaperst+sstest@gmail.com\n\
EMAIL_ACTIONS=msaperst+sstest@gmail.com\n\
EMAIL_SELECTS=msaperst+sstest@gmail.com\n\
EMAIL_CONTRACTS=msaperst+sstest@gmail.com\n\
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
                },
                "Gmail Credentials File": {
                    stage('Setup Gmail Credentials File') {
                        withCredentials([string(credentialsId: 'msaperst-gmail-credentials', variable: 'credentials')]) {
                            sh "echo '${credentials}' > credentials.json"
                        }
                    }
                },
                "Gmail Token File": {
                    stage('Setup Gmail Token File') {
                        withCredentials([string(credentialsId: 'msaperst-gmail-auth-token', variable: 'token')]) {
                            sh "echo '${token}' > token.json"
                        }
                    }
                }
        )
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

def launchBrowserDocker(browser) {
    withCredentials([
            usernamePassword(
                    credentialsId: 'docker-hub',
                    usernameVariable: 'dockerUser',
                    passwordVariable: 'dockerPass'
            )
    ]) {
        sh "docker login -u ${dockerUser} -p ${dockerPass}"
    }
    sh "export BROWSER=$browser; composer ui-pre-test;"
    sh '''while ! curl -sSL "http://localhost:4444/wd/hub/status" 2>&1 \
                  | jq -r '.value.ready' 2>&1 | grep "true" >/dev/null; do
              echo 'Waiting for the Grid'
              sleep 1
          done'''   // from https://github.com/SeleniumHQ/docker-selenium#using-a-bash-script-to-wait-for-the-grid
}

def closeBrowserDocker(browser) {
    sh "export BROWSER=$browser; composer ui-post-test;"
    sh 'docker logout'
}