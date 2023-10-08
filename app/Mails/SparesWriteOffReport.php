<?php

namespace App\Mails;

class SparesWriteOffReport extends BaseReport
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
        return $this->view('emails.spares_write_off')
            ->subject('Write Off Items')
            ->to($this->receiver)
            ->attach($this->filePath);
    }
}
