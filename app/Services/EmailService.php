<?php

namespace App\Services;

use App\Jobs\SendEmailNotificationJob;

class EmailService
{
    public static function sendEmail($recipientEmail, $message)
    {
        SendEmailNotificationJob::dispatch($recipientEmail, $message);
    }
}
