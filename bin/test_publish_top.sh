#! /bin/bash

for ((i=1;i<=100000000;i++));
do
   # your-unix-command-here
   # device_id=$($i % 60)
   # echo $device_id
   mosquitto_pub -h duongnd -p 1883 -t device/weight/$(($i % 60)) -m '{"device_id":1,"weight":100}'
done
