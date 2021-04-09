FROM php:7.4-fpm
ENV DEBIAN_FRONTEND=noninteractive
ENV PHPREDIS_VERSION 3.0.0
# debian packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    htop \
    wget \
    lynx \
    curl \
    mc \
    vim \
    libmcrypt-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libxml2-dev \
    unzip \
    locales \
    tzdata \
    nano

# Locale
RUN sed -i -e \
  's/# ru_RU.UTF-8 UTF-8/ru_RU.UTF-8 UTF-8/' /etc/locale.gen \
   && locale-gen

ENV LANG ru_RU.UTF-8
ENV LANGUAGE ru_RU:ru
ENV LC_LANG ru_RU.UTF-8
ENV LC_ALL ru_RU.UTF-8

# localtime
ENV TZ='Europe/Volgograd'
RUN echo $TZ > /etc/timezone \
    && rm /etc/localtime \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && dpkg-reconfigure -f noninteractive tzdata

# php pecl modules
RUN pecl install xdebug-2.8.1 mcrypt-1.0.3 \
    && docker-php-ext-enable xdebug mcrypt

# php composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# replace fpm port because of xdebug port 9000
RUN sed -i 's/9000/9001/' /usr/local/etc/php-fpm.d/zz-docker.conf

RUN mkdir -p /usr/src/php/ext/redis \
    && curl -L https://github.com/phpredis/phpredis/archive/$PHPREDIS_VERSION.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1 \
    && echo 'redis' >> /usr/src/php-available-exts \
    && docker-php-ext-install redis

# cleanup
RUN apt-get clean \
    && rm -fr /var/lib/apt/lists/* \
    && rm -fr /tmp/* \
    && rm -fr /var/tmp/*
WORKDIR /app/checker