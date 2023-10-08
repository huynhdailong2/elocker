<?php
/**
 * Created by PhpStorm.
 * User: salman
 * Date: 2/22/19
 * Time: 1:29 PM
 */

return [

    'host' => env('MQTT_HOST', '127.0.0.1'),
    'password' => env('MQTT_PASSWORD', ''),
    'username' => env('MQTT_USERNAME', ''),
    'certfile' => env('MQTT_CERT_FILE', ''),
    'port' => env('MQTT_PORT', '1883'),
    'debug' => env('MQTT_DEBUG', false) //Optional Parameter to enable debugging set it to True
];
