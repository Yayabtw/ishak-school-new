# Dockerfile pour Symfony avec PHP-FPM
FROM php:8.2-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers composer
COPY composer*.json ./

# Installer les dépendances PHP
RUN composer install --no-scripts --no-autoloader

# Copier le reste des fichiers
COPY . .

# Générer l'autoloader
RUN composer dump-autoload --optimize

# Changer les permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposer le port 9000 pour PHP-FPM
EXPOSE 9000

CMD ["php-fpm"] 