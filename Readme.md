# Projet Millesima Emailing
## Requirements
- Docker
- Composer & php7.2+
## Setup
- Clone repository
- Launch Docker using docker-compose up -d
- Execute _**composer install**_ outside of any container
- Setup class/millesima_bdd.php according to your needs or use included dump in /bdd
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

## Getting Production Ready
- Setup cron* files on project root in a crontab
- Setup mails on server so that sales, marketing etc.. can be aware of their tasks
- Edit /class/millesima_abstract.php to setup Host server
- Test stuffs before deploying ...

## Todos
- Save PromotionCards to database
- Create a task to copy tinyclues/archivage on internal network
- Ugrade Slim Library as we're using a very old one
- Migrate database to mysql 8.0 using modern encoding utf8mb4
- Change usage of library/PHPExcel to vendor/phpoffice


