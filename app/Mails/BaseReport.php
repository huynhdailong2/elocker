<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Http\Services\SettingService;

class BaseReport extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function getSenderConfig()
    {
        $settingService = new SettingService;
        return $settingService->getSenderEmail();
    }
}

