#!/bin/bash

service cron start

rm -rf /var/run/apache2/

# START SUPERVISOR.
exec /usr/bin/supervisord -n -c /etc/supervisord.conf
