# Use PHP + Apache
FROM php:8.2-apache

# Enable extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files into the container
COPY . /var/www/html/
