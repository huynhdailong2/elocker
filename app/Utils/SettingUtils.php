<?php

namespace App\Utils;

use App\Models\Setting;
use App\Consts;
use Exception;

class SettingUtils
{
    public static function validateMailSender()
    {
        $emailSender = Setting::where('key', Consts::SENDER_EMAIL_KEY)->first();
        $passwordSender = Setting::where('key', Consts::SENDER_PASSWORD_KEY)->first();

        if (!$emailSender || !$passwordSender) {
            throw new Exception('Please go to the Notification of the Maintenance module to config mail sender.');
        }

        return  true;
    }
}
