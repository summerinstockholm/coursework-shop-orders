FROM php:8.4.20-apache-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mysqli \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*