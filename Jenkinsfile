def workspace
def branch
def baseVersion
def version
def pullRequest
def refspecs
def branchCheckout

node() {
    workspace = pwd()
//    branch = env.BRANCH_NAME.replaceAll(/\//, "-")
//    baseVersion = "${env.BUILD_NUMBER}"
//    version = "$branch-$baseVersion"
    env.PROJECT = "saperstone-studios"
    pullRequest = env.CHANGE_ID
    if (pullRequest) {
        branchCheckout = "pr/${pullRequest}"
        refspecs = '+refs/pull/*/head:refs/remotes/origin/pr/*'
    } else {
        branchCheckout = env.BRANCH_NAME
        refspecs = '+refs/heads/*:refs/remotes/origin/*'
    }
    branchCheckout = 'docker'
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
    stage('Install Dependencies') {
        sh "composer install"
    }
    stage('Run Unit Tests') {
        sh "vendor/phpunit/phpunit/phpunit tests/ --log-junit reports/junit.xml --coverage-clover reports/clover.xml --coverage-html reports/html --whitelist src/"
    }
    stage('Run Sonar Analysis') {
        sh """sonar-scanner \
               -Dsonar.projectKey=saperstone-studios \
               -Dsonar.projectName='Saperstone Studios' \
               -Dsonar.projectVersion=2.0 \
               -Dsonar.sources=./bin,./public,./src,./templates \
               -Dsonar.tests=./tests \
               -Dsonar.exclusions=public/js/jqBootstrapValidation.js \
               -Dsonar.php.tests.reportPath=./reports/junit.xml \
               -Dsonar.php.coverage.reportPaths=./reports/clover.xml"""
    }
}