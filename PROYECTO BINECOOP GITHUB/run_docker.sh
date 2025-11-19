#!/bin/bash

IMAGE="nombredeejemplo"
CONTAINER="miapache"

function build_image() {
    echo "Construyendo imagen Docker..."
    docker build -t $IMAGE .
}

function run_container() {
    echo "Corriendo contenedor en puerto 8080..."
    docker run -it --rm -p 8080:80 --name $CONTAINER $IMAGE
}

while true; do
    echo ""
    echo "===== MENU DOCKER ====="
    echo "1 - Construir y correr"
    echo "2 - Solo construir"
    echo "3 - Solo correr"
    echo "CTRL + C para salir y detener el contenedor (si está corriendo)"
    echo "========================"
    read -p "Elegí una opción: " option

    case $option in
        1)
            build_image
            run_container
            ;;
        2)
            build_image
            ;;
        3)
            run_container
            ;;
        *)
            echo "Opción inválida."
            ;;
    esac
done
