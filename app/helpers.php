<?php

use App\Consts;
use Carbon\Carbon;
use App\Utils;

if (!function_exists('base64url_encode')) {
    function base64url_encode($data = '')
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

if (!function_exists('utcToClient')) {
    function utcToClient($value, $pattern = 'd/m/Y H:i:s', $timezone = null) {
        if (empty($value)) {
            return $value;
        }

        if (!$timezone) {
            $timezone = env('CLIENT_TIMEZONE');
        }
        return Carbon::parse($value)->setTimezone($timezone)->format($pattern);
    }
}

if (!function_exists('getOriginalClientIp')) {
    function getOriginalClientIp()
    {
        $request = request();

        $originalClientIp = $request->header('x-forwarded-for');

        if (empty($originalClientIp)) {
            $ip = $request->ip();
        } else {
            $ip = $originalClientIp;
        }

        $clientIPs = explode(Consts::CHAR_COMMA, $ip);
        return is_array($clientIPs) ? $clientIPs[0] : $clientIPs;
    }
}

if (!function_exists('toNumber')) {
    function toNumber($value) {
        return Utils::trimFloatNumber($value);
    }
}
