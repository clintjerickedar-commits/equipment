<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/** php artisan mail:test someone@gmail.com  -> sends a real test email and prints the exact error if it fails. */
class MailTest extends Command
{
    protected $signature = 'mail:test {email : Address that should receive the test email}';
    protected $description = 'Send a test email and show the real SMTP error if it fails';

    public function handle(): int
    {
        $driver = config('mail.default');
        $this->line("Mail driver  : <info>{$driver}</info>");

        if ($driver === 'smtp') {
            $cfg  = config('mail.mailers.smtp');
            $user = (string) ($cfg['username'] ?? '');
            $this->line('SMTP host    : ' . ($cfg['host'] ?? '-') . ':' . ($cfg['port'] ?? '-'));
            $this->line('SMTP user    : ' . ($user !== '' ? (str_contains($user, '@') ? $this->maskEmail($user) : $user) : '<error>(empty)</error>'));
            $this->line('SMTP password: ' . (!empty($cfg['password']) ? 'set (' . strlen($cfg['password']) . ' characters)' : '<error>(empty)</error>'));
            $this->line('From address : ' . config('mail.from.address'));
        }

        if (in_array($driver, ['log', 'array'], true)) {
            $this->warn("MAIL_MAILER is '{$driver}', so nothing is delivered. Set MAIL_MAILER=smtp in .env, run `php artisan config:clear`, then try again.");
            return self::FAILURE;
        }

        try {
            Mail::raw('This is a test email from the Equipment Borrowing System. If you can read this, email sending works.', function ($m) {
                $m->to($this->argument('email'))->subject('Test email - Equipment Borrowing System');
            });
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $this->error('FAILED: ' . $msg);

            if (str_contains($msg, '535') || stripos($msg, 'not accepted') !== false || stripos($msg, 'Authentication') !== false) {
                $this->warn('Hint: Gmail rejected the login. Use a 16-character App Password (not your normal password), with no spaces. 2-Step Verification must be on.');
            } elseif (stripos($msg, 'timed out') !== false || stripos($msg, 'could not be established') !== false) {
                $this->warn('Hint: the connection is blocked. Check your internet, antivirus/firewall for port 587, or try MAIL_PORT=465 with MAIL_SCHEME=smtps.');
            } elseif (stripos($msg, 'certificate') !== false || stripos($msg, 'SSL') !== false || stripos($msg, 'crypto') !== false) {
                $this->warn('Hint: PHP cannot verify Gmail\'s TLS certificate. In php.ini (XAMPP: C:\\xampp\\php\\php.ini) make sure `extension=openssl` is enabled and `openssl.cafile` points to a cacert.pem, then restart the server.');
            } elseif (stripos($msg, 'resolve') !== false || stripos($msg, 'getaddrinfo') !== false) {
                $this->warn('Hint: cannot reach smtp.gmail.com. Check your internet connection.');
            }
            return self::FAILURE;
        }

        $this->info('Sent! Check the inbox (and Spam) of ' . $this->argument('email') . '.');
        return self::SUCCESS;
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = strlen($local) > 2
            ? substr($local, 0, 1) . str_repeat('*', max(3, strlen($local) - 2)) . substr($local, -1)
            : substr($local, 0, 1) . '***';

        return $visible . '@' . $domain;
    }
}
