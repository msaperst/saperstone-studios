def branch
def version
def refspecs
def branchCheckout
def dockerRepo = "victor:9086"
def dockerRegistry = "${dockerRepo}/saperstone-studios"

node() {
    cleanWs()
    ansiColor('xterm') {
        env.BRANCH_NAME = 'master'
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
            // Get the test code from Gitblit repository
            checkout([
                    $class           : 'GitSCM',
                    branches         : [[name: "*/${branchCheckout}"]],
                    userRemoteConfigs: [[
                                                url          : 'http://victor/gitblit/r/saperstone-studios.git',
                                                refspec      : "${refspecs}",
                                                credentialsId: '33c0a87f-a4c8-4736-9598-e8898458ce89'
                                        ]]
            ])
        }
        stage('Install PHPUnit') {
            sh "wget -q -O phpunit https://phar.phpunit.de/phpunit-7.phar"
            sh "chmod +x phpunit"
        }
        stage('Run Unit Tests') {
            sh "./phpunit tests/unit/ --log-junit reports/junit.xml --coverage-clover reports/clover.xml --coverage-html reports/html --whitelist src/"
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
                -Dsonar.php.coverage.reportPaths=./reports/clover.xml"""
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
                    "Kill Old PHP Container": {
                        killContainer("saperstonestudios_php")
                    },
                    "Kill Old PHP Admin Container": {
                        killContainer("saperstonestudios_php-myadmin")
                    },
                    "Kill Old MySQL Container": {
                        killContainer("saperstonestudios_mysql")
                    }
            )
        }
        stage('Launch Docker Container') {
            sh "docker-compose up --build -d"
        }
        stage('Functional Tests') {
            parallel(
                    "Integration Tests": {
                        stage('Run Integration Tests') {
                            //TODO
                        }
                    },
                    "API Tests": {
                        stage('Run API Tests') {
                            try {
                                sh "mvn clean verify"
                            } catch (e) {
                                // throw e
                            } finally {
                                junit 'target/failsafe-reports/TEST-*.xml'
                                publishHTML([
                                        allowMissing         : false,
                                        alwaysLinkToLastBuild: true,
                                        keepAll              : true,
                                        reportDir            : 'target/failsafe-reports',
                                        reportFiles          : 'report.html',
                                        reportName           : 'API Test Report'
                                ])
                            }
                        }
                    },
                    "UI Tests": {
                        stage('Run UI Tests') {
                            //TODO
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
                        sh "docker tag workspace_php ${dockerRegistry}/php:${version}"
                        sh "docker push ${dockerRegistry}/php:${version}"
                        sh "docker tag workspace_php ${dockerRegistry}/php:latest"
                        sh "docker push ${dockerRegistry}/php:latest"
                    },
                    "PHP MyAdmin": {
                        sh "docker tag phpmyadmin/phpmyadmin ${dockerRegistry}/php-myadmin:${version}"
                        sh "docker push ${dockerRegistry}/php-myadmin:${version}"
                        sh "docker tag phpmyadmin/phpmyadmin ${dockerRegistry}/php-myadmin:latest"
                        sh "docker push ${dockerRegistry}/php-myadmin:latest"
                    },
                    "MySQL": {
                        sh "docker tag workspace_mysql ${dockerRegistry}/mysql:${version}"
                        sh "docker push ${dockerRegistry}/mysql:${version}"
                        sh "docker tag workspace_mysql ${dockerRegistry}/mysql:latest"
                        sh "docker push ${dockerRegistry}/mysql:latest"
                    }
            )
            sh "docker system prune -a"
            sh "docker logout ${dockerRepo}"
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
            newFile = file.take(file.lastIndexOf('.')) + ".min.$filetype"
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