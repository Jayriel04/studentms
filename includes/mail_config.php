<?php
// Mail configuration for the application.
// Values are read from environment variables if available, otherwise the
// defaults below are used. Replace defaults with real SMTP credentials.

$MAIL_HOST = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
$MAIL_USERNAME = getenv('MAIL_USERNAME') ?: 'senilla.jayriel.mcc@gmail.com';
$MAIL_PASSWORD = getenv('MAIL_PASSWORD') ?: 'wlnq kfuh fpbn cnpr';
$MAIL_PORT = getenv('MAIL_PORT') ?: 587;
$MAIL_ENCRYPTION = getenv('MAIL_ENCRYPTION') ?: 'tls'; // 'ssl' or 'tls' or ''
$MAIL_FROM = getenv('MAIL_FROM') ?: 'senilla.jayriel.mcc@gmail.com';
$MAIL_FROM_NAME = getenv('MAIL_FROM_NAME') ?: 'StudentVue';

/*
Example Gmail (use app password):
 $MAIL_HOST = 'smtp.gmail.com';
 $MAIL_USERNAME = 'your.email@gmail.com';
 $MAIL_PASSWORD = 'your-app-password';
 $MAIL_PORT = 587;
 $MAIL_ENCRYPTION = 'tls';

Example SendGrid:
 $MAIL_HOST = 'smtp.sendgrid.net';
 $MAIL_USERNAME = 'apikey';
 $MAIL_PASSWORD = 'YOUR_SENDGRID_API_KEY';
 $MAIL_PORT = 587;
 $MAIL_ENCRYPTION = 'tls';
*/

// Keep file small and not exit; included by PHP pages.
