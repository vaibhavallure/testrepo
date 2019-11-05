FROM phpdockerio/php73-fpm:latest
WORKDIR "/application"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install  php-memcached php7.3-mysql php-redis php-xdebug php7.3-gd php-imagick php7.3-intl php7.3-ldap php-mongodb php7.3-soap php-ssh2 php7.3-xmlrpc php7.3-xsl php-yaml php-mysql \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install crontab for app based on srv-zend's as of 02/10/2019
RUN apt-get update && apt-get -y install cron
COPY millesima-emailing-cron /etc/cron.d/millesima-emailing-cron
RUN chmod 0644 /etc/cron.d/millesima-emailing-cron
RUN crontab /etc/cron.d/millesima-emailing-cron
RUN touch /var/log/cron.log
CMD cron && tail -f /var/log/cron.log