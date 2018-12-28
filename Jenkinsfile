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
        // commenting out as not needed without UT/ITs
        // sh "composer install"
    }
    stage('Run Unit Tests') {
        // commenting out as UTs don't work as they rely on DB
        // sh "vendor/phpunit/phpunit/phpunit tests/ --log-junit reports/junit.xml --coverage-clover reports/clover.xml --coverage-html reports/html --whitelist src/"
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
        compress('js')
        compress('css')
        sh "rm ./public/img/main/*"
        sh "rm ./public/img/reviews/*"
        sh "rm ./public/wedding/img/*"
        sh "rm ./public/portrait/img/*"
        sh "rm ./public/commercial/img/*"
    }
    stage('Install Plugins') {
        //install jSignature plugin
        sh "cd $workspace/public/js; mkdir jSignature; cd jSignature; wget --quiet http://willowsystems.github.io/jSignature/jSignature.zip; unzip -qq jSignature.zip; rm jSignature.zip;"
        //install mPDF
        def mpdfVersion = "6.1.0"
        sh "cd $workspace/resources; mkdir mPDF; cd mPDF; wget --quiet https://github.com/mpdf/mpdf/releases/download/v${mpdfVersion}/02-mPDF-v${mpdfVersion}-without-examples.zip; unzip -qq 02-mPDF-v${mpdfVersion}-without-examples.zip; rm 02-mPDF-v${mpdfVersion}-without-examples.zip;"
        //install/configure paypal plugin
        def paypalVersion = "3.9.1"
        sh "cd $workspace/resources; wget --quiet https://github.com/paypal/merchant-sdk-php/archive/v${paypalVersion}.tar.gz; tar -xf v${paypalVersion}.tar.gz; rm v${paypalVersion}.tar.gz;"
        sh "cd $workspace/resources/merchant-sdk-php-${paypalVersion}/samples; php -f install.php; sed -i 's/mode = sandbox/mode = live/' sdk_config.ini; sed -i 's/acct1.UserName =/acct1.UserName = la_api1.saperstonestudios.com/' sdk_config.ini; sed -i 's/acct1.Password =/acct1.Password = 8X4VK4N2LJKCKWMZ/' sdk_config.ini; sed -i 's/acct1.Signature =/acct1.Signature = A5E8XrygZ.s0QlQk.FeDSEXnQbElAmZQu1A3lwY5E1gAqDowdsLWF34r/' sdk_config.ini;"
        //install/configure ua parser plugin
        sh "cd $workspace/resources; wget --quiet https://github.com/cbschuld/Browser.php/archive/master.zip; unzip -qq master.zip; rm master.zip;"
        //install/configure twitter plugin
        def twitterVersion = "3.1.0"
        sh "cd $workspace/resources; wget --quiet https://github.com/jublonet/codebird-php/archive/${twitterVersion}.tar.gz; tar -xf ${twitterVersion}.tar.gz; rm ${twitterVersion}.tar.gz;"
    }
    stage('Build Docker Container') {
        //build docker containers
        sh "docker-compose build"
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