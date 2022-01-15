FROM crunchgeek/php-fpm:7.3

# Define working directory
WORKDIR /etc/supervisor/conf.d

# Use local configuration
COPY laravel-worker.conf.tpl /etc/supervisor/conf.d/laravel-worker.conf.tpl
COPY laravel-horizon.conf.tpl /etc/supervisor/conf.d/laravel-horizon.conf.tpl
COPY supervisord-watchdog.py /opt/supervisord-watchdog.py

# Copy scripts
COPY init.sh /usr/local/bin/init.sh

# Copy files
COPY / /var/www/html
COPY /.env /var/www/html/.env

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html/
RUN composer install --no-dev

RUN cat .env

# Chmod
RUN chmod -R 777 storage/

# Migrations
#RUN php artisan migrate --force

# Add crontab file in the cron directory
ADD crontab /etc/cron.d/cron
RUN chmod 0644 /etc/cron.d/cron
RUN touch /var/log/cron.log

# Run supervisor
#ENTRYPOINT ["/bin/bash", "/usr/local/bin/init.sh"]
#CMD ["/usr/bin/supervisord --nodaemon --configuration /etc/supervisor/supervisord.conf"]
CMD ["supervisord --nodaemon --configuration /etc/supervisor/supervisord.conf"]
