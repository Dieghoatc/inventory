FROM ubuntu:latest

RUN apt-get update --fix-missing
RUN apt-get install software-properties-common curl -y
RUN add-apt-repository -y ppa:ondrej/php && apt-get update
RUN apt-get install nginx php7.3 php7.3-xdebug php7.3-fpm php7.3-common php7.3-common php7.3-mysql php7.3-mbstring php7.3-xml php7.3-zip php7.3-gd php7.3-curl php7.3-pdo php7.3-sqlite supervisor nodejs -y && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get install npm -y
RUN npm install -g yarn

COPY . /var/www/html/
COPY Docker/nginx/site.conf /etc/nginx/sites-available/site.conf
COPY Docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY Docker/conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN ln -s /etc/nginx/sites-available/site.conf /etc/nginx/sites-enabled/

WORKDIR /var/www/html

RUN composer install --no-scripts --no-autoloader

EXPOSE 80

CMD ["supervisord"]
