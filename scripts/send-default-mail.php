<?php

use Illuminate\Support\Facades\Mail;

Mail::raw('Failover mailer test from Hithaadhoo School website.', function ($message) {
    $message->to('azzam9000@gmail.com')->subject('Hithaadhoo School - Failover Test');
});

echo "Sent via default mailer OK\n";
