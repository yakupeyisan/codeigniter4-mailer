<?php

namespace Yakupeyisan\CodeIgniter4\Mailer;

use Yakupeyisan\CodeIgniter4\Mailer\Config\Mailer as MailerConfig;
use Yakupeyisan\CodeIgniter4\Mailer\Contracts\MailerInterface;
use Yakupeyisan\CodeIgniter4\Mailer\Drivers\DriverInterface;
use Yakupeyisan\CodeIgniter4\Mailer\Drivers\SmtpDriver;
use Yakupeyisan\CodeIgniter4\Mailer\Drivers\MailDriver;
use Yakupeyisan\CodeIgniter4\Mailer\Drivers\SendmailDriver;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\ConfigurationException;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

class Mailer implements MailerInterface
{
    protected MailerConfig $config;
    protected DriverInterface $driver;
    protected ?string $driverName = null;

    public function __construct(?string $driver = null)
    {
        $this->config = config('Mailer');
        $this->driverName = $driver ?? $this->config->defaultDriver;
        $this->driver = $this->createDriver($this->driverName);
    }

    /**
     * Driver oluştur
     */
    protected function createDriver(string $driver): DriverInterface
    {
        return match ($driver) {
            'smtp' => new SmtpDriver($this->config),
            'mail' => new MailDriver($this->config),
            'sendmail' => new SendmailDriver($this->config),
            default => throw new ConfigurationException("Bilinmeyen driver: {$driver}"),
        };
    }

    /**
     * Email gönder
     */
    public function send($to, string $subject, string $message, array $options = []): bool
    {
        $builder = new EmailBuilder($this->config);
        
        // To
        if (is_array($to)) {
            $builder->to($to);
        } else {
            $builder->to($to);
        }
        
        // Subject ve message
        $builder->subject($subject);
        
        // HTML veya text?
        if (isset($options['html']) && $options['html']) {
            $builder->html($message);
        } else {
            $builder->text($message);
        }
        
        // Diğer seçenekler
        if (isset($options['from'])) {
            if (is_array($options['from'])) {
                $builder->from($options['from'][0], $options['from'][1] ?? null);
            } else {
                $builder->from($options['from']);
            }
        }
        
        if (isset($options['cc'])) {
            $builder->cc($options['cc']);
        }
        
        if (isset($options['bcc'])) {
            $builder->bcc($options['bcc']);
        }
        
        if (isset($options['replyTo'])) {
            $builder->replyTo($options['replyTo']);
        }
        
        if (isset($options['attachments'])) {
            foreach ($options['attachments'] as $attachment) {
                if (is_array($attachment)) {
                    $builder->attach($attachment['path'], $attachment['name'] ?? null, $attachment['mime'] ?? null);
                } else {
                    $builder->attach($attachment);
                }
            }
        }
        
        return $this->sendEmail($builder);
    }

    /**
     * Email gönder ve sonucu döndür
     */
    public function sendWithResult($to, string $subject, string $message, array $options = []): array
    {
        $builder = new EmailBuilder($this->config);
        
        // To
        if (is_array($to)) {
            $builder->to($to);
        } else {
            $builder->to($to);
        }
        
        // Subject ve message
        $builder->subject($subject);
        
        // HTML veya text?
        if (isset($options['html']) && $options['html']) {
            $builder->html($message);
        } else {
            $builder->text($message);
        }
        
        // Diğer seçenekler
        if (isset($options['from'])) {
            if (is_array($options['from'])) {
                $builder->from($options['from'][0], $options['from'][1] ?? null);
            } else {
                $builder->from($options['from']);
            }
        }
        
        if (isset($options['cc'])) {
            $builder->cc($options['cc']);
        }
        
        if (isset($options['bcc'])) {
            $builder->bcc($options['bcc']);
        }
        
        if (isset($options['replyTo'])) {
            $builder->replyTo($options['replyTo']);
        }
        
        if (isset($options['attachments'])) {
            foreach ($options['attachments'] as $attachment) {
                if (is_array($attachment)) {
                    $builder->attach($attachment['path'], $attachment['name'] ?? null, $attachment['mime'] ?? null);
                } else {
                    $builder->attach($attachment);
                }
            }
        }
        
        return $this->sendEmailWithResult($builder);
    }

    /**
     * EmailBuilder ile email gönder
     */
    public function sendEmail(EmailBuilder $builder): bool
    {
        try {
            // Validation
            $this->validateEmail($builder);
            
            // Logging
            if ($this->config->logging) {
                $this->logEmail($builder, 'sending');
            }
            
            // Send
            $result = $this->driver->send($builder);
            
            // Logging
            if ($this->config->logging) {
                $this->logEmail($builder, $result ? 'sent' : 'failed');
            }
            
            return $result;
        } catch (\Exception $e) {
            if ($this->config->logging) {
                $this->logEmail($builder, 'error', $e->getMessage());
            }
            throw new EmailException("Email gönderilemedi: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * EmailBuilder ile email gönder ve sonucu döndür
     */
    public function sendEmailWithResult(EmailBuilder $builder): array
    {
        try {
            // Validation
            $this->validateEmail($builder);
            
            // Logging
            if ($this->config->logging) {
                $this->logEmail($builder, 'sending');
            }
            
            // Send
            $result = $this->driver->sendWithResult($builder);
            
            // Logging
            if ($this->config->logging) {
                $this->logEmail($builder, $result['success'] ? 'sent' : 'failed', $result['message'] ?? null);
            }
            
            return $result;
        } catch (\Exception $e) {
            if ($this->config->logging) {
                $this->logEmail($builder, 'error', $e->getMessage());
            }
            throw new EmailException("Email gönderilemedi: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Email validation
     */
    protected function validateEmail(EmailBuilder $builder): void
    {
        if (empty($builder->getTo())) {
            throw new EmailException("Alıcı (to) belirtilmelidir");
        }
        
        if (empty($builder->getSubject())) {
            throw new EmailException("Konu (subject) belirtilmelidir");
        }
        
        if (empty($builder->getHtmlBody()) && empty($builder->getTextBody())) {
            throw new EmailException("Email içeriği (html veya text) belirtilmelidir");
        }
        
        if (empty($builder->getFromEmail())) {
            throw new EmailException("Gönderen (from) belirtilmelidir");
        }
    }

    /**
     * Email logla
     */
    protected function logEmail(EmailBuilder $builder, string $status, ?string $message = null): void
    {
        $logPath = $this->config->logPath;
        
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $logFile = $logPath . 'email-' . date('Y-m-d') . '.log';
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => $status,
            'driver' => $this->driverName,
            'to' => $builder->getTo(),
            'subject' => $builder->getSubject(),
            'from' => [
                'email' => $builder->getFromEmail(),
                'name' => $builder->getFromName(),
            ],
            'message' => $message,
        ];
        
        $logLine = json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND);
    }

    /**
     * Test bağlantısı
     */
    public function test(): bool
    {
        return $this->driver->test();
    }

    /**
     * Batch email gönder
     */
    public function sendBatch(array $emails): array
    {
        $results = [];
        $count = 0;
        
        foreach ($emails as $email) {
            if ($count >= $this->config->batchLimit) {
                usleep($this->config->batchDelay * 1000);
                $count = 0;
            }
            
            if ($email instanceof EmailBuilder) {
                $results[] = [
                    'email' => $email->getTo(),
                    'result' => $this->sendEmail($email),
                ];
            } else {
                $results[] = [
                    'email' => $email,
                    'result' => false,
                    'error' => 'EmailBuilder instance bekleniyor',
                ];
            }
            
            $count++;
        }
        
        return $results;
    }

    /**
     * Driver'ı değiştir
     */
    public function driver(string $driver): self
    {
        $this->driverName = $driver;
        $this->driver = $this->createDriver($driver);
        return $this;
    }

    /**
     * Mevcut driver'ı döndür
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }
}

