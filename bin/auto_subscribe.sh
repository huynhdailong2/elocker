#! /bin/bash

while [ true ]; do
    exist=$(netstat -plnt | grep "1883")
    if [ ! -z "$exist" ];then
        echo '333';
        /home/duongnd/Documents/work/workspace/sample/basic-drk-inventory/bin/start_server.sh;
        break;
    else
        echo '111';
    fi
done
