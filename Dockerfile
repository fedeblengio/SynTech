FROM centos:7

RUN yum update -y && yum install epel-release -y && yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm -y && yum clean all -y && rm -rf /var/cache/yum

RUN yum --enablerepo=remi-php73 -y install httpd php php-bcmath php56-php-fpm php-cli php-common php-gd php-intl php-ldap php-fpm  php-mbstring php-mysqlnd php-pear php-soap php-xml php-xmlrpc php-zip && yum clean all -y && rm -rf /var/cache/yum

RUN echo -e "<VirtualHost *:80>\n DocumentRoot /var/www/html/public \n ServerName default \n <Directory /var/www/html/public> \n Options +Indexes +FollowSymLinks \n AllowOverride all \n  Require all granted \n RewriteEngine On \n </Directory> \n </VirtualHost>"  > /etc/httpd/conf.d/laravel.conf

RUN yum install php-cli php-zip wget unzip -y && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php --install-dir=/usr/local/bin --filename=composer


WORKDIR /var/www/html

COPY package*.json ./
COPY .env.example .env
COPY . .

RUN  echo 'TLS_REQCERT never' > /etc/openldap/ldap.conf
CMD ["php-fpm", "-F"]

EXPOSE 8000


RUN composer install && php artisan key:generate && chown -R apache /var/www/html/bootstrap/cache && chown -R apache /var/www/html/storage
CMD php artisan serve --host 0.0.0.0
