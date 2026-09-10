<?php

use Illuminate\Support\Facades\Mail;

config([
    'mail.mailers.smtp_test' => [
        'transport' => 'smtp',
        'host' => env('MAIL_HOST'),
        'port' => (int) env('MAIL_PORT'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'verify_peer' => false,
    ],
]);

Mail::mailer('smtp_test')->raw(
    'This is a test email from the Hithaadhoo School website.',
    function ($message) {
        $message->to('azzam9000@gmail.com')->subject('Hithaadhoo School - Test Email');
    }
);

echo "Sent OK\n";
