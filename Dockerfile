FROM centos:7

RUN yum update -y && yum install epel-release -y && yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm -y && yum clean all -y && rm -rf /var/cache/yum

RUN yum --enablerepo=remi-php73 -y install httpd php php-bcmath php-cli php-common php-gd php-intl php-ldap php-mbstring php-mysqlnd php-pear php-soap php-xml php-xmlrpc php-zip && yum clean all -y && rm -rf /var/cache/yum

RUN echo -e "<VirtualHost *:80>\n DocumentRoot /var/www/html/public \n ServerName default \n <Directory /var/www/html/public> \n Options +Indexes +FollowSymLinks \n AllowOverride all \n  Require all granted \n RewriteEngine On \n </Directory> \n </VirtualHost>"  > /etc/httpd/conf.d/laravel.conf

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && php composer-setup.php && php -r "unlink('composer-setup.php');" && mv composer.phar /bin/composer && chmod +x /bin/composer


WORKDIR /var/www/html



CMD composer install && php artisan key:generate && chown -R apache /var/www/html/bootstrap/cache && chown -R apache /var/www/html/storage && php artisan serve --host 0.0.0.0
