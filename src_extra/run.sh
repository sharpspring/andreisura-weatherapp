#!/bin/bash

timestamp=`date +%Y-%m-%dT%H:%M:%S`
echo "$timestamp: Importing weather data"

/usr/local/bin/php /var/www/public/cron/import.php
