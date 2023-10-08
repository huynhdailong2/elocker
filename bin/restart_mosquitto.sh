# bin/sh

container_name="basic_drk_inventory_mosquitto"
mosquitto=$(docker ps | grep "$container_name")
if [ ! -z "$mosquitto" ]
then
    # notify-send "[$container_name] Restarting..."
    docker restart "$container_name"
    docker exec -it -d basic_drk_inventory php artisan subscribe:topic
    # notify-send "[$container_name] Restarted"
fi
