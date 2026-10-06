<?php
// Laravel/Symfony use transport schemes, while older SMTP examples use tls/ssl.
$scheme = env('MAIL_SCHEME');
$scheme = match ($scheme) {
    'tls' => 'smtp',
    'ssl' => 'smtps',
    null, '' => (int) env('MAIL_PORT', 587) === 465 ? 'smtps' : 'smtp',
    default => $scheme,
};
return [
    // Only a real SMTP transport is configured: OTPs must never go to logs.
    'default' => env('MAIL_MAILER', 'smtp'),
    'mailers' => ['smtp' => [
        'transport' => 'smtp', 'scheme' => $scheme,
        'host' => env('MAIL_HOST'), 'port' => (int) env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME'), 'password' => env('MAIL_PASSWORD'),
        'timeout' => 15, 'require_tls' => true,
        'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
    ]],
    'from' => ['address' => env('MAIL_FROM_ADDRESS'), 'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Home Services'))],
];
