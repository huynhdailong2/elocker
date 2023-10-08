<?php

namespace App\Providers;

use Illuminate\Mail\TransportManager as BaseTransportManager;
use Swift_SmtpTransport as SmtpTransport;
use App\Http\Services\SettingService;
use App\Consts;

class TransportManager extends BaseTransportManager {

    /**
     * Create an instance of the SMTP Swift Transport driver.
     *
     * @return \Swift_SmtpTransport
     */
    protected function createSmtpDriver()
    {
        $config = $this->app->make('config')->get('mail');

        // The Swift SMTP transport instance will allow us to use any SMTP backend
        // for delivering mail such as Sendgrid, Amazon SES, or a custom server
        // a developer has available. We will just pass this configured host.
        $transport = new SmtpTransport($config['host'], $config['port']);

        if (isset($config['encryption'])) {
            $transport->setEncryption($config['encryption']);
        }

        // Once we have the transport we will check for the presence of a username
        // and password. If we have it we will set the credentials on the Swift
        // transporter instance so that we'll properly authenticate delivery.

        // if (isset($config['username'])) {
        //     $transport->setUsername($config['username']);

        //     $transport->setPassword($config['password']);
        // }

        $sender = (new SettingService())->getSenderEmail();
        $transport->setUsername($sender[Consts::SENDER_EMAIL_KEY]);
        $transport->setPassword($sender[Consts::SENDER_PASSWORD_KEY]);

        return $this->configureSmtpDriver($transport, $config);
    }
}
