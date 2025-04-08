# Use PHP with Apache
FROM php:8.2-apache

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy source files into Apache directory
COPY src/ /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Open port 80
EXPOSE 80
