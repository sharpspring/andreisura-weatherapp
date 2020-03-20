#!/bin/bash

echo "Starting Docker container"

# add a job 
echo "* * * * * /run.sh > /proc/1/fd/1 2>/proc/1/fd/2
# This extra line makes it a valid cron" > weather-cron

crontab weather-cron
crontab -l

cron -f & docker-php-entrypoint php-fpm

# Alternative:
# apt-get install supervisor
# COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf
# CMD ["/usr/bin/supervisord"]
#[program:cron]
#command = cron -f
#
#[program:php]
#command = docker-php-entrypoint php-fpm


