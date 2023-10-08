<?php

namespace App\Mails;

class YetToReturnSparesReport extends BaseReport
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
        return $this->view('emails.yet_to_return_spares')
            ->subject('Yet To Return Items')
            ->to($this->receiver)
            ->attach($this->filePath);
    }
}
