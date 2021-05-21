<p align="center"><img src="https://i.pinimg.com/originals/1e/ae/0d/1eae0d90b1256075eba5e84ea755fb33.jpg" width="400"></a></p>

# Trelloo

https://trello.com/invite/b/0cFZvwRZ/74e25bf1661230e35cfbc2c82945f10c/syntech

# Miro
https://miro.com/app/board/o9J_lLThV_s=/
# Ejecutar con Docker

Requermientos:

Docker :
Ejecutar: # ```docker-compose up```

- ```docker exec -ti laravel_app /bin/bash```

 - ```php artisan key:generate;php artisan config:cache;php artisan migrate:fresh;```

- ```php artisan config:clear;php artisan cache:clear;php artisan config:cache;```

- ```php artisan ldap:test```

- cd /etc/ldap/

- sudo su
- echo 'TLS_REQCERT never' > ldap.conf
- chmod -R 777 /app/storage/logs/*
- ```php artisan serve```

# Entrar A Mysql
- ```docker exec -ti laravel_mysql mysql -u root```

# Comandos php artisan make:

- ```php artisan make:controller nombreDelControlador``` # CREAR UN CONTROLADOR

- ```php artisan make:model nombreDelModelo``` # CREA UN MODELO

- ``` php artisan make:migration nombreDeLaMigracion ``` # CREA UNA MIGRACION

- ```php artisan migrate``` # CARGA LAS NUEVAS TABLAS

- ```php artisan serve``` # EJECUTA SERVER

prueba tstt

