# #! /bin/bash

# ./before_start_server.sh

# current_dir=$(pwd)
# parent_dir="$(dirname "$current_dir")"

# docker-compose up -d
# docker exec -it -d basic_drk_inventory php artisan subscribe:topic
"${PWD%/*}"
php artisan subscribe:topic
