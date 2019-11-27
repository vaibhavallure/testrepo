# Projet Millesima Emailing

## Warning
As of 18/11 the sendmail config does not apply to php-fpm containre on boot/restart
- please edit container hosts as specified in phpdocker/php-fpm/php-ini-overrides.ini
- execute sendmailconfig command in the php-fpm container
- test with command 'echo "test" | sendmail -v youremail@yourdomain.com

## Requirements
- Docker & docker-compose
- Composer & php7.2+ with enabled extensions soap, gd, mysql, curl, zip, dom, mbstring

## Containers
- millesima-emailing-php-fpm responsible for php-fpm service
- millesima-emailing-mysql responsible for mysql database
- millesima-emailing-webserver responsible for nginx webserver service
- millesima-emailing-mailhog responsible for mailhog service
- millesima-emailing-cron responsible for cron deamon
## Setup
- Clone repository
- Launch Docker using docker-compose up -d
- Execute _**composer install**_ outside of any container
- Setup class/millesima_bdd.php according to your needs or use included dump in /bdd
- Edit /class/millesima_abstract.php to setup DOCKER_HOST_IP and DOCKER_HOST_PORT (localhost and 8080 for dev)
- Acces project via http://localhost:8080

## Restoring included database dump
- open bash in php-fpm container
> docker exec -it millesima-emailing-php-fpm bash

- decompress bdd archive
> bzip2 -d bdd/backup_db_emailing_25092019.sql.bz2

- exit container
>  exit

- Login to mysql container and load dump

> docker exec -it millesima-emailing-mysql mysql -uroot -pmillesima
>
> mysql> use emailing;
>
> mysql> source bdd/backup_db_emailing_25092019.sql
>
> mysql> exit
>
> ByeBye
>
As of 2019/11/27 you are required to update database scheme by running query in bdd/db_upgrade_20192711.sql to handle promotionCards

## Using phpMyAdmin
If you want to use phpmyadmin :
- cd into project directory
- composer create-project phpmyadmin/phpmyadmin (outside of container)
- cp phpdocker/phpmyadmin/config.inc.php phpmyadmin/
- adjust phpmyadmin/config.inc.php to your needs
- access via http://localhost:8080/phpmyadmin/

## Exporting emailing database from srv-zend
- ssh into srv-zend
- switch to su
- create dump using
> mysqldump -u root -p[password] --default-character-set=latin1 emailing -r backup_db_emailing_[date].sql

## Using mailhog
- By default container is using mhsendmail to send internal emails to mailhog
- you can access mailhog via http://localhost:8025
- check phpdocker/php-fpm/php-ini-overrides.ini 
- see section below

## Access php logs
> docker exec -it millesima-emailing-webserver sh
>
> cd /var/log/nginx/
- check your stuffs 

## Update products (table baseok)
- Ask Pick to put csv file base_complete.csv in bdd folder (using a samba share)
- Ask then Pick to call wget -O - http://srv-v-zend:8080/LaunchSqlBaseProduit.php
- Launch this process using Pick Menu : 7/10/6/12 
- Rework this process asap...

## Getting Production Ready
- Edit /class/millesima_abstract.php to setup DOCKER_HOST_IP and DOCKER_HOST_PORT
- Chmod 777 writable folders : bdd, fichiers, smarty, 
- Share folders using samba 
- Setup sendmail on server Cf : sendmailconfig and php-ini-overrides.ini, test with 
> echo "test" | sendmail -v "youremail@millesima.com"
- Test stuffs before deploying ...

## Todos
- Save PromotionCards to database
- Create a task to copy tinyclues/archivage on internal network
- Ugrade Slim Library as we're using a very old one
- Migrate database to mysql 8.0 using modern encoding utf8mb4
- Change usage of library/PHPExcel to vendor/phpoffice


