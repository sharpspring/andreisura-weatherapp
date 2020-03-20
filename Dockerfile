# https://docs.docker.com/develop/develop-images/dockerfile_best-practices/

# Notes:
#   Only the instructions RUN, COPY, ADD create layers. 
#   Other instructions create temporary intermediate images, and do not increase the size of the build.

FROM php:7.2-fpm
# LABEL testing="true"

# Set working directory
# WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    # cron \
    # curl \
    # git \
    # unzip \
    # zip \
    vim

# Clear cache
# RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql

# COPY php.ini /usr/local/etc/php


# Add dedicated user for more safety
# RUN groupadd -g 1000 www
# RUN useradd -u 1000 -ms /bin/bash -g www www

# ADD run.sh /run.sh
# ADD entrypoint.sh /entrypoint.sh
# RUN chmod +x /run.sh /entrypoint.sh

# ADD cron.allow /etc/cron.d/cron.allow

# Copy app content
# COPY . /var/www
COPY src/ /var/www/site

# Copy permissions
# COPY --chown=www:www . /var/www/site

# Run using the dedicated user
# USER www

# Expose port 9000 and start php-fpm server
# EXPOSE 9000
#CMD ["php-fpm"]

# Run two things: php-fpm && cron
# USER root

# https://docs.docker.com/engine/reference/builder/#entrypoint
# ENTRYPOINT /entrypoint.sh


