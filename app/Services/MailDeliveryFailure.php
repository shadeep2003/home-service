<?php
namespace App\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\QueryException;
use Illuminate\View\ViewException;

/** Returns predefined labels only; never return SMTP messages or debug transcripts. */
class MailDeliveryFailure
{
    public static function reason(\Throwable $exception): string
    {
        if ($exception instanceof \Symfony\Component\Mailer\Exception\UnsupportedSchemeException) { return 'smtp_scheme_unsupported'; }
        if ($exception instanceof LockTimeoutException) { return 'delivery_lock_timeout'; }
        if ($exception instanceof QueryException) { return 'challenge_storage_failed'; }
        if ($exception instanceof ViewException) { return 'email_template_failed'; }
        $message = strtolower($exception->getMessage());
        return match (true) {
            str_contains($message, 'failed to authenticate'),
            str_contains($message, 'could not be authenticated'),
            str_contains($message, 'username and password not accepted') => 'smtp_authentication_rejected',
            str_contains($message, 'certificate'), str_contains($message, 'crypto'),
            str_contains($message, 'starttls') => 'smtp_tls_failed',
            str_contains($message, 'timed out') => 'smtp_timeout',
            str_contains($message, 'getaddrinfo') => 'smtp_dns_failed',
            str_contains($message, 'connection refused'), str_contains($message, 'could not connect'),
            str_contains($message, 'network is unreachable') => 'smtp_connection_failed',
            default => 'mail_delivery_failed',
        };
    }
}
