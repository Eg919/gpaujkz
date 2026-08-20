<?php

namespace App\Jobs;

use App\Mail\EmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $recipientEmail;
    public string $messageContent;

    /**
     * Create a new job instance.
     */
    public function __construct(string $recipientEmail, string $messageContent)
    {
        $this->recipientEmail = $recipientEmail;
        $this->messageContent = $messageContent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->recipientEmail)
            ->send(new EmailNotification($this->recipientEmail, $this->messageContent));
    }
}
