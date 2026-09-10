<?php

use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

// Quick SMTP AUTH probe for both Outlook hosts (bypasses Laravel).
require __DIR__.'/../vendor/autoload.php';

// Read credentials from .env so no secrets live in this script.
$env = [];
foreach (file(__DIR__.'/../.env') as $line) {
    if (preg_match('/^(MAIL_USERNAME|MAIL_PASSWORD)=(.*)$/', trim($line), $m)) {
        $env[$m[1]] = trim($m[2], "\"'");
    }
}
$user = $env['MAIL_USERNAME'] ?? '';
$pass = $env['MAIL_PASSWORD'] ?? '';
if ($user === '' || $pass === '') {
    exit("Missing MAIL_USERNAME/MAIL_PASSWORD in .env\n");
}

foreach (['smtp-mail.outlook.com', 'smtp.office365.com'] as $host) {
    $transport = new EsmtpTransport($host, 587, false);
    $transport->setUsername($user);
    $transport->setPassword($pass);
    $transport->getStream()->setStreamOptions(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
    try {
        $transport->start();
        echo "$host: AUTH OK\n";
        $transport->stop();
    } catch (Throwable $e) {
        echo "$host: FAIL - ".substr($e->getMessage(), 0, 220)."\n";
    }
}
