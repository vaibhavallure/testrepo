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
                    sh 'php --version'
                    sh 'git remote add sync git@github.com:vaibhavallure/testrepo.git'
                    sh 'git checkout allure-dev3'
             	    sh 'git pull'
             	    sh 'git push sync allure-dev3'
                }
        }

    }
}
