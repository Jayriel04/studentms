# Use the official PHP image with Apache
FROM php:8.2-apache

# CRITICAL STEP: Install system dependencies for PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install the PostgreSQL PDO driver and the base PDO extension
RUN docker-php-ext-install pdo pdo_pgsql

# Set the working directory
WORKDIR /var/www/html

# Copy all your project files
COPY . .

# Enable the mod_rewrite module
RUN a2enmod rewrite

# The container will listen on port 80 by default
