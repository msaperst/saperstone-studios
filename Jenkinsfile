def workspace
def branch
def baseVersion
def version
def pullRequest
def refspecs
def branchCheckout

node() {
    workspace = pwd()
    env.BRANCH_NAME = 'docker'
    branch = env.BRANCH_NAME.replaceAll(/\//, "-")
    baseVersion = "${env.BUILD_NUMBER}"
    version = "$branch-$baseVersion"
    env.PROJECT = "saperstone-studios"
    pullRequest = env.CHANGE_ID
    if (pullRequest) {
        branchCheckout = "pr/${pullRequest}"
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
    stage('Install Dependencies') {
        sh "composer install"
    }
    stage('Run Unit Tests') {
         sh "vendor/phpunit/phpunit/phpunit tests/unit/ --log-junit reports/junit.xml --coverage-clover reports/clover.xml --coverage-html reports/html --whitelist src/"
    }
    stage('Prep Files') {
        compress('js')
        compress('css')
    }
    stage('Build Docker Container') {
        sh "docker-compose build"
    }
    stage('Launch Docker Container') {
        sh "docker-compose up"
    }
    stage('Run Integration Tests') {
        //TODO
    }
    stage('Run API Tests') {
        sh "mvn clean verify"
    }
    stage('Run UI Tests') {
        //TODO
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
}

def compress(filetype) {
    Random rnd = new Random()
    def random = rnd.nextInt(9999999)
    def output = sh returnStdout: true, script: "ls ./public/$filetype/"
    def files = output.split()
    files.remove("mpdf.css") //TODO - not working?
    files.each { file ->
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