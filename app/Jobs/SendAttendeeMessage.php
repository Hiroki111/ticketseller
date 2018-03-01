<?php

namespace App\Jobs;

use App\Mail\AttendeeMessageEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAttendeeMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $attendeeMessage;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($attendeeMessage)
    {
        $this->attendeeMessage = $attendeeMessage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->attendeeMessage->withChunkedRecipients(20, function ($recipients) {
            $recipients->each(function ($recipient) {
                Mail::to($recipient)->queue(new AttendeeMessageEmail($this->attendeeMessage));
            });
        });
    }
}
