<?php

use Modules\AdminMailServer\Support\MailServerConfigurator;

test('mail configurator always sets smtp transport and symfony dsn scheme', function (): void {
    MailServerConfigurator::apply([
        'mail_protocol' => 'smtp',
        'mail_sender_email' => 'noreply@mlhub.vn',
        'mail_sender_name' => 'MKT',
        'smtp_server' => 'smtp.emailit.com',
        'smtp_username' => 'emailit',
        'smtp_password' => 'secret',
        'smtp_port' => '587',
        'smtp_encryption' => 'tls',
        'mail_timeout' => '30',
        'mail_ehlo_domain' => 'mlhub.vn',
        'sendmail_path' => '/usr/sbin/sendmail -bs -i',
    ]);

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.transport'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.scheme'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.emailit.com')
        ->and(config('mail.mailers.smtp.encryption'))->toBe('tls');

    // Building the mailer must not throw Unsupported mail transport [].
    expect(fn () => app('mail.manager')->mailer('smtp'))->not->toThrow(InvalidArgumentException::class);
});

test('mail configurator maps ssl encryption to smtps dsn scheme', function (): void {
    MailServerConfigurator::apply([
        'mail_protocol' => 'smtp',
        'mail_sender_email' => 'noreply@mlhub.vn',
        'mail_sender_name' => 'MKT',
        'smtp_server' => 'smtp.emailit.com',
        'smtp_username' => 'emailit',
        'smtp_password' => 'secret',
        'smtp_port' => '465',
        'smtp_encryption' => 'ssl',
        'mail_timeout' => '30',
        'mail_ehlo_domain' => 'mlhub.vn',
        'sendmail_path' => '/usr/sbin/sendmail -bs -i',
    ]);

    expect(config('mail.mailers.smtp.scheme'))->toBe('smtps')
        ->and(config('mail.mailers.smtp.transport'))->toBe('smtp');
});

test('dsnSchemeFromEnv maps legacy tls values to smtp', function (): void {
    expect(MailServerConfigurator::dsnSchemeFromEnv('tls'))->toBe('smtp')
        ->and(MailServerConfigurator::dsnSchemeFromEnv('ssl'))->toBe('smtps')
        ->and(MailServerConfigurator::dsnSchemeFromEnv('smtp'))->toBe('smtp')
        ->and(MailServerConfigurator::dsnSchemeFromEnv(''))->toBe('smtp');
});
