pipeline {
    agent { node { label 'master' } }
    stages {
        stage('build') {
        when { branch 'developement' }
            steps {
                sh 'php --version'
                sh '/root/scripts/millesima-deploy.sh millesima-dev php72'
            }
        }
        stage('publish') {
            when { branch 'allure-dev1' }
                steps {
		    sh '/root/scripts/millesima-publish.sh'
                }
        }

    }
}
