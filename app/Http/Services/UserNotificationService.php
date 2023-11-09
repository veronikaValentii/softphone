<?php


namespace App\Http\Services;


use App\Mail\ActivationAccountMail;
use App\Models\UserConfirmation;
use Illuminate\Support\Facades\Mail;

class UserNotificationService
{
    public static function sendSms()
    {

    }

    public static function sendEmail(string $email, UserConfirmation $confirmation): void
    {
        Mail::to($email)->send(new ActivationAccountMail($confirmation));
    }

}
