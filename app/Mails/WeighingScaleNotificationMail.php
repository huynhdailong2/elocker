<?php


namespace App\Mails;


use App\Consts;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class WeighingScaleNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $listBinsNotification;

    public $receivers = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($listBinsNotification, $receivers = [])
    {
        $this->listBinsNotification = $listBinsNotification;
        $this->receivers = $receivers;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // If list receivers is empty => Send to administrators
        if(!$this->receivers) {
            $this->receivers = $this->getListEmailReceivers();
        }

        return $this->view('emails.weighing_scale_notification')
            ->subject('Weighing Scale Notification')
            ->to($this->receivers);
    }

    private function getListEmailReceivers()
    {
        $listEmail = User::query()
            ->whereIn('role', [Consts::USER_ROLE_SUPER_ADMIN, Consts::USER_ROLE_ADMINISTRATOR])
            ->pluck('email')
            ->all();

        return array_filter($listEmail);
    }
}
