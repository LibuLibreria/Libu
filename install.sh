#!/bin/bash




while true; do
    read -p "¿Desea continuar? (s/n)" sn
    case $sn in
        [Ss]* ) sh instalarnuevo.sh; break;;
        [Nn]* ) exit;;
        * ) echo "Por favor conteste con las letras 's' o 'n'.";;
    esac
done