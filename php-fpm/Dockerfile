FROM php:8.2-fpm

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

# Install app from apt
RUN apt-get update && apt-get install -y  \
    nodejs  \
    yarn  \
    git  \
    unzip  \
    wget  \
    nano  \
    ssh-client

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install php extensions
RUN apt-get install -y --no-install-recommends \
    libssl-dev  \
    zlib1g-dev  \
    libxml2-dev  \
    libpq-dev  \
    libzip-dev  \
    libpng-dev  \
    libjpeg-dev  \
    libfreetype-dev  \
    libmagickwand-dev \
    libz-dev  \
    libsodium-dev  \
    libmemcached-dev

RUN docker-php-ext-install sockets

# Clear apt list and temp files
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install pecl dependency
RUN pecl install xdebug &&  \
    pecl install imagick -o -f &&  \
    pecl install apcu

# Configure php dependency
RUN docker-php-ext-configure soap &&  \
    docker-php-ext-configure gd --with-freetype --with-jpeg

# Install php dependencies
RUN docker-php-ext-install -j$(nproc)  \
    pdo_mysql  \
    pdo_pgsql  \
    mysqli  \
    gd  \
    sodium  \
    pgsql  \
    intl  \
    opcache  \
    soap \
    zip \
    sysvsem

# Enable php dependencies
RUN docker-php-ext-enable imagick &&  \
    docker-php-ext-enable apcu

RUN docker-php-ext-install calendar

RUN apt-get update && \
    apt-get install -y libyaml-dev && \
    pecl install yaml && \
    docker-php-ext-enable yaml

# Install required dependencies
RUN apt-get update && apt-get install -y build-essential wget

# Download GMP source
RUN wget https://gmplib.org/download/gmp/gmp-6.2.1.tar.xz

# Extract source
RUN tar -xf gmp-6.2.1.tar.xz

# Compile and install GMP
RUN cd gmp-6.2.1 && ./configure && make && make install

# Link GMP libraries
RUN ln -s /usr/local/lib/libgmp.so /usr/lib/libgmp.so

# Enable PHP GMP extension
RUN docker-php-ext-install gmp


RUN usermod -u 1000 www-data

# Add configs
ADD configs/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www

EXPOSE 9000