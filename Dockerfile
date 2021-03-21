FROM php:7-apache

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN docker-php-ext-install pdo pdo_mysql

ADD src/ /timetracker/src

RUN rm -f /timetracker/src/main/php/run/logger.ini
RUN rm -f /timetracker/src/main/php/run/logger.ini.default
RUN rm -f /timetracker/src/main/php/run/database.ini
RUN rm -f /timetracker/src/main/php/run/database.ini.default


RUN ln -s /timetracker/src/main/php/run/CreateLoginEntry.php /var/www/html/CreateLoginEntry.php
RUN ln -s /timetracker/src/main/php/run/CreateLogoutEntry.php /var/www/html/CreateLogoutEntry.php
RUN ln -s /timetracker/src/main/php/run/ExportMonth.php /var/www/html/ExportMonth.php
RUN ln -s /timetracker/src/main/php/run/ReplicateEntry.php /var/www/html/ReplicateEntry.php
RUN ln -s /timetracker/src/main/php/run/TimeOfDay.php /var/www/html/TimeOfDay.php

RUN mkdir /log
