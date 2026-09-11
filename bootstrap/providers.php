<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\MailOAuthServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    MailOAuthServiceProvider::class,
];
