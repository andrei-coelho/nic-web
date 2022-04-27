FROM php:8.0.2-apache

ARG USE_C_PROTOBUF=true

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y git unzip zip && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev libxml2 zlib1g-dev git unzip

RUN cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/

RUN docker-php-ext-install bcmath
RUN docker-php-ext-install pdo pdo_mysql

RUN docker-php-ext-configure gd --with-freetype --with-jpeg 

RUN docker-php-ext-install gd

#RUN pecl install grpc
#RUN echo 'extension=grpc.so' >> $PHP_INI_DIR/conf.d/grpc.ini

#RUN if [ "$USE_C_PROTOBUF" = "false" ]; then echo 'Using PHP implementation of Protobuf'; else echo 'Using C implementation of Protobuf'; pecl install protobuf; echo 'extension=protobuf.so' >> $PHP_INI_DIR/conf.d/protobuf.ini; fi

WORKDIR /var/www/html

COPY . .