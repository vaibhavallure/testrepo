pipeline {
    agent { node { label 'development' } }
    stages {
        stage('build') {
            steps {
                sh 'php --version'
                sh '/root/scripts/millesima-deploy.sh millesima-dev php72'
            }
        }
    }
}
