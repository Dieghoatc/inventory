FROM ubuntu:latest

RUN apt-get update --fix-missing
RUN apt-get install software-properties-common curl -y
RUN add-apt-repository -y ppa:ondrej/php && apt-get update
RUN apt-get install nginx php7.2 php7.2-xdebug php7.2-fpm php7.2-common php7.2-common php7.2-mysql php7.2-mbstring php7.2-xml php7.2-zip php7.2-gd php7.2-curl php7.2-pdo php7.2-sqlite supervisor nodejs -y && \
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
