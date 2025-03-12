<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\EmailNotification;

class EmailService
{
    public static function sendEmail($recipientEmail, $message)
    {
        Mail::to($recipientEmail)->send(new EmailNotification($recipientEmail, $message));
    }
}
