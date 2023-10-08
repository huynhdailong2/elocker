#! /bin/bash

# Kill all ports before start the server.
declare -a all_ports=('1883' '3306' '8000')

for port in "${all_ports[@]}"
do
    exist_port=$(sudo netstat -plnt | grep "$port")
    if [ ! -z "$exist_port" ]
    then
        sudo kill $(sudo lsof -t -i:"$port")
    fi
done
