<?php

namespace Yakupeyisan\CodeIgniter4\Mailer;

use Yakupeyisan\CodeIgniter4\Mailer\Config\Mailer as MailerConfig;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

class EmailBuilder
{
    protected MailerConfig $config;
    protected array $to = [];
    protected array $cc = [];
    protected array $bcc = [];
    protected array $replyTo = [];
    protected ?string $fromEmail = null;
    protected ?string $fromName = null;
    protected ?string $returnPath = null;
    protected ?string $subject = null;
    protected ?string $htmlBody = null;
    protected ?string $textBody = null;
    protected array $attachments = [];
    protected array $embeddedImages = [];
    protected array $headers = [];
    protected ?int $priority = null;
    protected string $charset = 'UTF-8';
    protected int $wordWrap = 76;
    protected bool $validateEmails = true;

    public function __construct(?MailerConfig $config = null)
    {
        $this->config = $config ?? config('Mailer');
        $this->charset = $this->config->charset;
        $this->wordWrap = $this->config->wordWrap;
        $this->validateEmails = $this->config->validateEmails;
        $this->priority = $this->config->priority;
        
        // Varsayılan from
        $this->from($this->config->from['email'], $this->config->from['name']);
        
        // Varsayılan reply-to
        if ($this->config->replyTo) {
            $this->replyTo($this->config->replyTo);
        }
        
        // Varsayılan return-path
        if ($this->config->returnPath) {
            $this->returnPath($this->config->returnPath);
        }
    }

    /**
     * Alıcı ekle
     */
    public function to(string|array $email, ?string $name = null): self
    {
        if (is_array($email)) {
            foreach ($email as $addr) {
                if (is_array($addr)) {
                    $this->to($addr[0], $addr[1] ?? null);
                } else {
                    $this->to($addr);
                }
            }
        } else {
            if ($this->validateEmails && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new EmailException("Geçersiz email adresi: {$email}");
            }
            $this->to[] = ['email' => $email, 'name' => $name];
        }
        return $this;
    }

    /**
     * CC ekle
     */
    public function cc(string|array $email, ?string $name = null): self
    {
        if (is_array($email)) {
            foreach ($email as $addr) {
                if (is_array($addr)) {
                    $this->cc($addr[0], $addr[1] ?? null);
                } else {
                    $this->cc($addr);
                }
            }
        } else {
            if ($this->validateEmails && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new EmailException("Geçersiz email adresi: {$email}");
            }
            $this->cc[] = ['email' => $email, 'name' => $name];
        }
        return $this;
    }

    /**
     * BCC ekle
     */
    public function bcc(string|array $email, ?string $name = null): self
    {
        if (is_array($email)) {
            foreach ($email as $addr) {
                if (is_array($addr)) {
                    $this->bcc($addr[0], $addr[1] ?? null);
                } else {
                    $this->bcc($addr);
                }
            }
        } else {
            if ($this->validateEmails && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new EmailException("Geçersiz email adresi: {$email}");
            }
            $this->bcc[] = ['email' => $email, 'name' => $name];
        }
        return $this;
    }

    /**
     * Gönderen bilgisi
     */
    public function from(string $email, ?string $name = null): self
    {
        if ($this->validateEmails && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new EmailException("Geçersiz email adresi: {$email}");
        }
        $this->fromEmail = $email;
        $this->fromName = $name;
        return $this;
    }

    /**
     * Reply-To ekle
     */
    public function replyTo(string|array $email, ?string $name = null): self
    {
        if (is_array($email)) {
            foreach ($email as $addr) {
                if (is_array($addr)) {
                    $this->replyTo($addr[0], $addr[1] ?? null);
                } else {
                    $this->replyTo($addr);
                }
            }
        } else {
            if ($this->validateEmails && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new EmailException("Geçersiz email adresi: {$email}");
            }
            $this->replyTo[] = ['email' => $email, 'name' => $name];
        }
        return $this;
    }

    /**
     * Return-Path ayarla
     */
    public function returnPath(string $email): self
    {
        if ($this->validateEmails && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new EmailException("Geçersiz email adresi: {$email}");
        }
        $this->returnPath = $email;
        return $this;
    }

    /**
     * Konu
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * HTML body
     */
    public function html(string $html): self
    {
        $this->htmlBody = $html;
        return $this;
    }

    /**
     * Plain text body
     */
    public function text(string $text): self
    {
        $this->textBody = $text;
        return $this;
    }

    /**
     * Template kullanarak HTML body oluştur
     */
    public function view(string $template, array $data = []): self
    {
        $view = \Config\Services::renderer();
        $html = $view->setData($data)->render($template);
        $this->htmlBody = $html;
        return $this;
    }

    /**
     * Dosya ekle
     */
    public function attach(string $path, ?string $name = null, ?string $mimeType = null): self
    {
        if (!file_exists($path)) {
            throw new EmailException("Dosya bulunamadı: {$path}");
        }
        
        $this->attachments[] = [
            'type' => 'file',
            'path' => $path,
            'name' => $name ?? basename($path),
            'mime' => $mimeType ?? $this->getMimeType($path),
        ];
        
        return $this;
    }

    /**
     * String içeriği ekle
     */
    public function attachData(string $data, string $name, ?string $mimeType = null): self
    {
        $this->attachments[] = [
            'type' => 'data',
            'data' => $data,
            'name' => $name,
            'mime' => $mimeType ?? 'application/octet-stream',
        ];
        
        return $this;
    }

    /**
     * Inline image ekle
     */
    public function embed(string $path, ?string $cid = null): string
    {
        if (!file_exists($path)) {
            throw new EmailException("Dosya bulunamadı: {$path}");
        }
        
        $cid = $cid ?? 'cid_' . uniqid();
        $mimeType = $this->getMimeType($path);
        
        $this->embeddedImages[] = [
            'type' => 'file',
            'path' => $path,
            'cid' => $cid,
            'mime' => $mimeType,
        ];
        
        return 'cid:' . $cid;
    }

    /**
     * String içeriğinden inline image ekle
     */
    public function embedData(string $data, string $mimeType, ?string $cid = null): string
    {
        $cid = $cid ?? 'cid_' . uniqid();
        
        $this->embeddedImages[] = [
            'type' => 'data',
            'data' => $data,
            'cid' => $cid,
            'mime' => $mimeType,
        ];
        
        return 'cid:' . $cid;
    }

    /**
     * Custom header ekle
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Priority ayarla (1-5, 1 = highest)
     */
    public function priority(int $priority): self
    {
        if ($priority < 1 || $priority > 5) {
            throw new EmailException("Priority 1-5 arasında olmalıdır");
        }
        $this->priority = $priority;
        return $this;
    }

    /**
     * Charset ayarla
     */
    public function charset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Word wrap uzunluğu ayarla
     */
    public function wordWrap(int $length): self
    {
        $this->wordWrap = $length;
        return $this;
    }

    /**
     * Email validation aç/kapat
     */
    public function validateEmails(bool $validate): self
    {
        $this->validateEmails = $validate;
        return $this;
    }

    /**
     * Email gönder
     */
    public function send(?string $driver = null): bool
    {
        $mailer = new Mailer($driver);
        return $mailer->sendEmail($this);
    }

    /**
     * Email gönder ve sonucu döndür
     */
    public function sendWithResult(?string $driver = null): array
    {
        $mailer = new Mailer($driver);
        return $mailer->sendEmailWithResult($this);
    }

    /**
     * Queue'ya ekle
     */
    public function queue(?string $driver = null): bool
    {
        if (!$this->config->queueEnabled) {
            throw new EmailException("Queue özelliği aktif değil");
        }
        
        // Queue implementasyonu burada yapılabilir
        // Şimdilik direkt gönder
        return $this->send($driver);
    }

    /**
     * Email verilerini array olarak döndür
     */
    public function toArray(): array
    {
        return [
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'replyTo' => $this->replyTo,
            'from' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName,
            ],
            'returnPath' => $this->returnPath,
            'subject' => $this->subject,
            'htmlBody' => $this->htmlBody,
            'textBody' => $this->textBody,
            'attachments' => $this->attachments,
            'embeddedImages' => $this->embeddedImages,
            'headers' => $this->headers,
            'priority' => $this->priority,
            'charset' => $this->charset,
            'wordWrap' => $this->wordWrap,
        ];
    }

    /**
     * MIME type belirle
     */
    protected function getMimeType(string $path): string
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($path);
        }
        
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $path);
            finfo_close($finfo);
            return $mime ?: 'application/octet-stream';
        }
        
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
            'html' => 'text/html',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    // Getters
    public function getTo(): array { return $this->to; }
    public function getCc(): array { return $this->cc; }
    public function getBcc(): array { return $this->bcc; }
    public function getReplyTo(): array { return $this->replyTo; }
    public function getFromEmail(): ?string { return $this->fromEmail; }
    public function getFromName(): ?string { return $this->fromName; }
    public function getReturnPath(): ?string { return $this->returnPath; }
    public function getSubject(): ?string { return $this->subject; }
    public function getHtmlBody(): ?string { return $this->htmlBody; }
    public function getTextBody(): ?string { return $this->textBody; }
    public function getAttachments(): array { return $this->attachments; }
    public function getEmbeddedImages(): array { return $this->embeddedImages; }
    public function getHeaders(): array { return $this->headers; }
    public function getPriority(): ?int { return $this->priority; }
    public function getCharset(): string { return $this->charset; }
    public function getWordWrap(): int { return $this->wordWrap; }
}

