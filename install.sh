#!/bin/bash

echo "Debe estar instalado php7.0-xml"
echo "Si no lo está, hacer un sudo apt-get install, o dará error"
echo "También debe instalarse php-intl"

while true; do
    read -p "¿Desea continuar? (s/n)" sn
    case $sn in
        [Ss]* ) sh instalarnuevo.sh; break;;
        [Nn]* ) exit;;
        * ) echo "Por favor conteste con las letras 's' o 'n'.";;
    esac
done
