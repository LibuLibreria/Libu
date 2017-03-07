#!/bin/bash

echo "====================="
echo "Creando base de datos"
echo "====================="
echo "Introduzca la contraseña para el nuevo usuario de la base de datos: "
read PASS

echo "Y ahora el sistema le pedirá la contraseña root de mysql"
mysql  -p -u root <<MYSQL_SCRIPT
CREATE DATABASE libudb;
CREATE USER 'user_libu'@'localhost' IDENTIFIED BY '$PASS';
GRANT ALL PRIVILEGES ON libudb.* TO 'user_libu'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

echo "Datos a introducir cuando se solicite:"
echo "Database:   libudb"
echo "Username:   user_libu"
echo "Password:   $PASS"

HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var

echo "===================================="
echo "Instalando dependencias con Composer"
echo "===================================="
php composer.phar install


echo "==============================="
echo "Actualizando las bases de datos"
echo "==============================="

mysql -p "$PASS" -u user_libu libudb < estructura_libudb.sql
php bin/console doctrine:migrations:migrate

echo "==============================="
echo "Actualizando enlaces simbólicos"
echo "==============================="
php bin/console assets:install web --symlink

echo "=================="
echo "Actualizando caché"
echo "=================="
php bin/console cache:clear --env=dev
php bin/console cache:clear --env=prod
