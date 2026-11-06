From php:8.3-apache


#libraries the mongodb driver needs to talk to atlas over TLS
RUN apt-get update && apt-get install -y --no-install-recommends \
        libssl-dev \
        libcurl4-openssl-dev \
        git \
        unzip \
    && rm -rf /var/lib/apt/lists/*

#mongodb PHP extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# composer copied from the oficial image 
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# make Apache serve the "public" folder
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache URL rewriting
RUN a2enmod rewrite

#Set the working folder inside the container
WORKDIR /var/www/html