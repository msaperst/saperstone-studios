def branch
def version
def refspecs
def branchCheckout
def dockerRepo = "victor:9086"
def dockerRegistry = "${dockerRepo}/saperstone-studios"

node() {
    cleanWs()
    ansiColor('xterm') {
        env.BRANCH_NAME = 'feature/sqlRework'       //TODO - clean me up!!!
        branch = env.BRANCH_NAME.replaceAll(/\//, "-")
        version = "$branch-${env.BUILD_NUMBER}"
        env.PROJECT = "saperstone-studios"
        if (env.CHANGE_ID) {
            branchCheckout = "pr/${env.CHANGE_ID}"
            refspecs = '+refs/pull/*/head:refs/remotes/origin/pr/*'
        } else {
            branchCheckout = env.BRANCH_NAME
            refspecs = '+refs/heads/*:refs/remotes/origin/*'
        }
        cleanWs()
        stage('Checkout Code') { // for display purposes
            // Get the test code from GitHub repository
            checkout([
                    $class           : 'GitSCM',
                    branches         : [[name: "*/${branchCheckout}"]],
                    userRemoteConfigs: [[
                                                url          : 'git@github.com:msaperst/saperstone-studios.git',
                                                refspec      : "${refspecs}",
                                                credentialsId: 'github'
                                        ]]
            ])
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
# paypal information\n\
PAYPAL_USERNAME=${paypalUser}\n\
PAYPAL_PASSWORD=${paypalPass}\n\
PAYPAL_SIGNATURE=${paypalSignature}' > .env"
            }
        }
        stage('Run Integration Tests') {
            try {
                sh "composer integration-pre-test"
                sh "composer integration-test"
            } finally {
                sh "composer integration-post-test"
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
            sh "docker-compose up --build -d"
        }
        stage('Clean Up') {
            sh "composer clean"
        }
        stage('Functional Tests') {
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
                    },
                    "Chrome UI Tests": {
                        stage('Run Chrome UI Tests') {
                            try {
                                timeout(60) {
                                    waitUntil {
                                        script {
                                            def r = sh returnStdout: true, script: 'curl -I http://localhost:90/ 2>/dev/null | head -n 1 | cut -d " " -f2'
                                            return (r.trim() == '200');
                                        }
                                    }
                                }
                                sh 'export BROWSER=chrome; composer ui-pre-test;'
                                sh 'sleep 10'
                                sh 'export BROWSER=chrome; COMPOSER_PROCESS_TIMEOUT=1200 composer ui-test;'
                            } finally {
                                sh 'export BROWSER=chrome; composer ui-post-test;'
                                junit 'reports/ui-chrome/ui-junit.xml'
                                publishHTML([
                                        allowMissing         : false,
                                        alwaysLinkToLastBuild: true,
                                        keepAll              : true,
                                        reportDir            : 'reports/ui-chrome/',
                                        reportFiles          : 'ui-results.html',
                                        reportName           : 'Chrome Test Results Report'
                                ])
                            }
                        }
                    }
            )
        }
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