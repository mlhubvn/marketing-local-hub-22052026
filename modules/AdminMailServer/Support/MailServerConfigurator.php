<?php

namespace Modules\AdminMailServer\Support;

use Illuminate\Mail\MailManager;
use Modules\AdminSettings\Support\OptionStore;

class MailServerConfigurator
{
    public static function fromOptions(OptionStore $options): array
    {
        return [
            'mail_protocol' => (string) $options->get('mail_protocol', config('mail.default', 'log')),
            'mail_sender_email' => (string) $options->get('mail_sender_email', config('mail.from.address', 'hello@example.com')),
            'mail_sender_name' => (string) $options->get('mail_sender_name', config('mail.from.name', config('app.name', 'Stackposts'))),
            'smtp_server' => (string) $options->get('smtp_server', config('mail.mailers.smtp.host', '')),
            'smtp_username' => (string) $options->get('smtp_username', config('mail.mailers.smtp.username', '')),
            'smtp_password' => (string) $options->get('smtp_password', config('mail.mailers.smtp.password', '')),
            'smtp_port' => (string) $options->get('smtp_port', (string) config('mail.mailers.smtp.port', 587)),
            'smtp_encryption' => (string) $options->get('smtp_encryption', (string) (config('mail.mailers.smtp.encryption') ?: 'tls')),
            'mail_timeout' => (string) $options->get('mail_timeout', (string) (config('mail.mailers.smtp.timeout') ?: '30')),
            'mail_ehlo_domain' => (string) $options->get('mail_ehlo_domain', (string) config('mail.mailers.smtp.local_domain', '')),
            'sendmail_path' => (string) $options->get('sendmail_path', (string) config('mail.mailers.sendmail.path', '/usr/sbin/sendmail -bs -i')),
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public static function apply(array $state): void
    {
        $transport = self::normalizeTransport($state['mail_protocol'] ?? null);
        $encryption = self::normalizeScheme($state['smtp_encryption'] ?? null);
        $dsnScheme = self::dsnScheme($encryption);
        $timeout = isset($state['mail_timeout']) && $state['mail_timeout'] !== ''
            ? (int) $state['mail_timeout']
            : null;
        $localDomain = trim((string) ($state['mail_ehlo_domain'] ?? ''));
        $sendmailPath = trim((string) ($state['sendmail_path'] ?? '/usr/sbin/sendmail -bs -i'));

        // Replace the full smtp mailer array so transport/scheme cannot be left empty
        // (Laravel/Symfony reject transport=[] and scheme="" / "tls").
        config([
            'mail.default' => $transport,
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'scheme' => $dsnScheme,
                'url' => null,
                'host' => trim((string) ($state['smtp_server'] ?? '')),
                'port' => (int) ($state['smtp_port'] ?? 587),
                'username' => trim((string) ($state['smtp_username'] ?? '')),
                'password' => (string) ($state['smtp_password'] ?? ''),
                'encryption' => $encryption,
                'timeout' => $timeout,
                'local_domain' => $localDomain !== '' ? $localDomain : null,
            ],
            'mail.mailers.sendmail' => [
                'transport' => 'sendmail',
                'path' => $sendmailPath !== '' ? $sendmailPath : '/usr/sbin/sendmail -bs -i',
            ],
            'mail.mailers.log' => [
                'transport' => 'log',
                'channel' => config('mail.mailers.log.channel'),
            ],
            'mail.from.address' => trim((string) ($state['mail_sender_email'] ?? 'hello@example.com')),
            'mail.from.name' => trim((string) ($state['mail_sender_name'] ?? config('app.name', 'Stackposts'))),
        ]);

        app(MailManager::class)->forgetMailers();
    }

    public static function normalizeTransport(mixed $transport): string
    {
        return match (strtolower(trim((string) $transport))) {
            'smtp' => 'smtp',
            'sendmail', 'mail' => 'sendmail',
            'log' => 'log',
            default => 'log',
        };
    }

    public static function normalizeScheme(mixed $scheme): ?string
    {
        return match (strtolower(trim((string) $scheme))) {
            'tls' => 'tls',
            'ssl' => 'ssl',
            default => null,
        };
    }

    /**
     * Symfony Mailer DSN scheme must be smtp|smtps (not tls/ssl/empty).
     */
    public static function dsnScheme(?string $encryption): string
    {
        return match ($encryption) {
            'ssl' => 'smtps',
            default => 'smtp',
        };
    }

    /**
     * Map env MAIL_SCHEME / legacy encryption values to a Symfony DSN scheme.
     */
    public static function dsnSchemeFromEnv(?string $value): string
    {
        return match (strtolower(trim((string) $value))) {
            'smtps', 'ssl' => 'smtps',
            default => 'smtp',
        };
    }
}
