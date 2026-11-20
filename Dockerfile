# Imagen única para desarrollo local y para Render.
# Render no tiene runtime nativo de PHP, por eso se despliega con Docker.
FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates libonig-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mbstring curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Evita que Apache sirva .env, la carpeta de pruebas o el esquema SQL.
RUN printf '%s\n' \
      '<FilesMatch "^\.env">' \
      '  Require all denied' \
      '</FilesMatch>' \
      '<Directory "/var/www/html/tests">' \
      '  Require all denied' \
      '</Directory>' \
      '<Directory "/var/www/html/database">' \
      '  Require all denied' \
      '</Directory>' \
      '<Directory "/var/www/html/includes">' \
      '  Require all denied' \
      '</Directory>' \
      > /etc/apache2/conf-available/almacen-security.conf \
    && a2enconf almacen-security

WORKDIR /var/www/html
COPY . /var/www/html/
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
