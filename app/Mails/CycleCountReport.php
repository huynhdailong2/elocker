<?php

namespace App\Mails;

class CycleCountReport extends BaseReport
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
        return $this->view('emails.cycle_count_report')
            ->subject('Cycle Count')
            ->to($this->receiver)
            ->attach($this->filePath);
    }
}
