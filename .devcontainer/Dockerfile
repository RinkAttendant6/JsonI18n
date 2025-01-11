FROM mcr.microsoft.com/devcontainers/php:1-8.2-bullseye

RUN apt-get update && apt install -y libicu-dev && \
    docker-php-ext-configure intl && docker-php-ext-install intl && docker-php-ext-enable intl
