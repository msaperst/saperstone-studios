def urlBranch
def workspace
def branch
def branchType
def baseVersion
def version
def pullRequest
def refspecs

node() {
    workspace = pwd()
    branch = env.BRANCH_NAME.replaceAll(/\//, "-")
    branchType = env.BRANCH_NAME.split(/\//)[0]
    urlBranch = env.BRANCH_NAME.replaceAll(/\//, "%252F")
    baseVersion = "${env.BUILD_NUMBER}"
    version = "$branch-$baseVersion"
    env.PROJECT = "saperstone-studios"
    def branchCheckout
    pullRequest = env.CHANGE_ID
    if (pullRequest) {
        branchCheckout = "pr/${pullRequest}"
        refspecs = '+refs/pull/*/head:refs/remotes/origin/pr/*'
    } else {
        branchCheckout = env.BRANCH_NAME
        refspecs = '+refs/heads/*:refs/remotes/origin/*'
    }
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
}