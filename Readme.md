# Projet Millesima Emailing
## Setup
- Clone repository
- Launch Docker using docker-compose up -d
- Execute _**composer install**_ within container millesima-emailing-php-fpm
- Setup class/millesima_bdd.php according to your needs or use included dump in /bdd
- Acces project via http://localhost:8080

## Restoring included database dump
- open bash in php-fpm container
> docker-exec -it millesima-emailing-php-fpm bash

- decompress bdd archive
> bzip2 -d bdd/backup_db_emailing_25092019.sql.bz2

- exit container
>  exit

- Login to mysql container and load dump

> docker-exec -it millesima-emailing-mysql mysql -uroot -pmillesima
>
> mysql> use emailing;
>
> mysql> source bdd/backup_db_emailing_25092019.sql
>
> mysql> exit
>
> ByeBye
