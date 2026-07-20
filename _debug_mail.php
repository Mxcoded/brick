<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
echo "Mail default: " . config('mail.default') . PHP_EOL;
echo "MAIL_MAILER env: " . env('MAIL_MAILER') . PHP_EOL;
echo "Reservations email: " . (config('mail.reservations_email') ?? 'NOT SET') . PHP_EOL;
