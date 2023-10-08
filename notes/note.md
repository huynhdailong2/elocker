mosquitto_pub -h localhost -p 1883 -t device/weight/1 -m '{"device_id":1,"weight":100}'
mosquitto_pub -h absolutech-bridge.local -p 1883 -t device/weight/1 -m '{"device_id":1,"weight":100}'


mosquitto_pub -h localhost -p 1883 -t device/weight/4 -m '{"device_id":1,"weight":105410}'

0 0 * * * /home/absolutech-bridgelocal/Sources/basic-drk-inventory/bin/restart_mosquitto.sh >/dev/null 2>&1

1377714726 / 83ws5k
1335119361 / juh571 / sw343y

792641988 / 5vc34e

192.168.123.44


drksystem.100
bind_address absolutech-bridge.local


Topics: device/weight/#
Payload: {"device_id":<PCB ID>,"weight",<sensor value>}


when PCB just booted up, it sends device/connected/<device_id>, with payload {"device_id",<device id>}

example: the PCB raw value = 100 and send to server.
user see this 100 raw and user can reference current raw = 100 is equal to Bin zero(empty) Tare weight and save in DB

so when user put 100 apples into the bin, PCB will send current raw value eg. 1100 raw value.
so user will press cal button and input 100 apple into the server. server process the data (1100-100/100apples)
and calculate 10 raw value/apple.

so when user take out 50 apples from the bin, pcb will have a current raw value of 600 raw value
and server calculate (600-100)/10 = 50 and display 50 apples in the current qty box.
600 is the total raw but we need to -100 because we tare(zero) the bin due to the weight of the bin.



511,6





first time, we will empty the bin and use press the [Tare] button. the system will take the [Current: Raw Value]
and saved it in the [Tare Weight:Raw Value]. Noted: [Tare Weight:Qty] will always be [0] since it is an empty bin.

second time. User put 10 apples in the bin and press [Cal Weight] button.
System will take [Current:Raw Value] and save it in [Cal Weight: Raw Value]. User then input [10] in the [Cal Weight: Qty].

system will calculate how many raw value/apple and system will take reference from the [Current:raw value]
and display the actual Current Qty.

user reset the bin? you mean calibration again? it will do step 1 and 2 as per my message above


Client 9 has exceeded timeout, disconnecting.


1567404612: Client 9 has exceeded timeout, disconnecting.
1567404672: New connection from 192.168.123.92 on port 1883.
1567404672: New client connected from 192.168.123.92 as 254 (p2, c1, k15).
1567404677: Client 200 has exceeded timeout, disconnecting.
1567409741: Socket error on client auto-6575BDD4-72FB-8547-FD9C-734FCC55E1DA, disconnecting.
1567409743: New client connected from ::ffff:172.18.0.1 as auto-31A8524D-2F91-6CBE-EB65-BBA10BC72D0B (p2, c1, k60).
1567409745: Socket error on client auto-31A8524D-2F91-6CBE-EB65-BBA10BC72D0B, disconnecting.
1567409746: New client connected from ::ffff:172.18.0.1 as auto-3A4CA35C-25C3-A6BA-9BE9-2BAE32394EE2 (p2, c1, k60).


Hi @Richard Kang on the old system, did you have the problem as below?
https://github.com/mqttjs/MQTT.js/issues/883


- Issue MQTT client exceeded timeout
	https://community.openenergymonitor.org/t/frequently-disconnects-from-mqtt-mosquitto-stop-feeds-updates/6074/7
	https://www.eclipse.org/lists/mosquitto-dev/msg01633.html



# /etc/systemd/system/basic-drk-inventory.service

# /etc/systemd/system/basic-drk-inventory.service

[Unit]
Description=Basic Drk Inventory System
Requires=mosquitto.service
After=mosquitto.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/etc/systemd/system/basic-drk-inventory.service
ExecStart=/etc/systemd/system/basic-drk-inventory.service/bin/start_server.sh
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target

*Note*: Directory of source must belongs to the Documents folder.

systemctl disable basic-drk-inventory
systemctl enable basic-drk-inventory

systemctl list-unit-files --type=service
sudo systemctl list-units --type=service

log_type debug

listener 1883

listener 9001
protocol websockets


/usr/bin/teamviewer

tail -f /var/log/mosquitto/mosquitto.log
tail -f /var/log/mosquitto/mosquitto.log | grep "H from 2 (d0, q0, r0, m0, 'device/weight/1'"


socket_domain ipv6



sudo add-apt-repository "deb [arch=amd64] https://repos.emqx.io/emqx-ce/deb/ubuntu/ $(lsb_release -cs) stable"



New MQTT Broker

https://docs.emqx.io/broker/v3/en/guide.html
https://www.emqx.io/downloads#broker


Morning Kelvin,
I try to check difference between the new computer and old computer but they no difference. I still check log MQTT server, I saw the speed is stable, about 4-5 seconds (you see the image below). I try to change Broker from Mosquitto to EMQ X Broker (it has low latency) but the speed is same
 ===> The speed 
