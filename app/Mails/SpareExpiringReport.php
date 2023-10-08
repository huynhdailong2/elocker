<?php

namespace App\Mails;

class SpareExpiringReport extends BaseReport
{

    protected $receiver;
    protected $filePath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($receiver, $filePath)
    {
        $this->receiver = $receiver;
        $this->filePath = $filePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.expiring_spares_report')
            ->subject('Expiring Items')
            ->to($this->receiver)
            ->attach($this->filePath);
    }
}
