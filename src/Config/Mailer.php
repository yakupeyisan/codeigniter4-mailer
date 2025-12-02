<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Config;

use CodeIgniter\Config\BaseConfig;

class Mailer extends BaseConfig
{
    /**
     * Varsayılan mail driver
     * Seçenekler: smtp, mail, sendmail
     */
    public string $defaultDriver = 'smtp';

    /**
     * SMTP Ayarları
     */
    public array $smtp = [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls', // tls, ssl, veya boş string
        'username' => '',
        'password' => '',
        'timeout' => 30,
        'keepAlive' => false,
        'auth' => true,
    ];

    /**
     * Sendmail Ayarları
     */
    public array $sendmail = [
        'path' => '/usr/sbin/sendmail',
        'flags' => '-bs',
    ];

    /**
     * Varsayılan gönderen bilgileri
     */
    public array $from = [
        'email' => 'noreply@example.com',
        'name' => 'CodeIgniter 4 Mailer',
    ];

    /**
     * Varsayılan reply-to adresi
     */
    public ?string $replyTo = null;

    /**
     * Varsayılan return-path adresi
     */
    public ?string $returnPath = null;

    /**
     * Email encoding
     */
    public string $charset = 'UTF-8';

    /**
     * Email priority (1-5, 1 = highest, 5 = lowest)
     */
    public int $priority = 3;

    /**
     * Word wrap uzunluğu
     */
    public int $wordWrap = 76;

    /**
     * Email template klasörü
     */
    public string $templatePath = APPPATH . 'Views/emails/';

    /**
     * Template cache aktif mi?
     */
    public bool $templateCache = false;

    /**
     * Email logging aktif mi?
     */
    public bool $logging = true;

    /**
     * Log dosyası yolu
     */
    public string $logPath = WRITEPATH . 'logs/emails/';

    /**
     * Queue aktif mi?
     */
    public bool $queueEnabled = false;

    /**
     * Queue driver
     */
    public string $queueDriver = 'database'; // database, redis, file

    /**
     * Batch gönderim için maksimum email sayısı
     */
    public int $batchLimit = 100;

    /**
     * Batch gönderim arası bekleme süresi (milisaniye)
     */
    public int $batchDelay = 100;

    /**
     * Email validation aktif mi?
     */
    public bool $validateEmails = true;

    /**
     * Embedded images için otomatik CID oluştur
     */
    public bool $autoEmbedImages = true;

    /**
     * X-Mailer header
     */
    public string $mailer = 'CodeIgniter 4 Mailer';

    /**
     * Constructor - .env dosyasından değerleri yükler
     */
    public function __construct()
    {
        parent::__construct();

        // Driver
        $this->defaultDriver = env('MAIL_DRIVER', $this->defaultDriver);

        // SMTP
        $this->smtp['host'] = env('MAIL_HOST', $this->smtp['host']);
        $this->smtp['port'] = (int)env('MAIL_PORT', $this->smtp['port']);
        $this->smtp['encryption'] = env('MAIL_ENCRYPTION', $this->smtp['encryption']);
        $this->smtp['username'] = env('MAIL_USERNAME', $this->smtp['username']);
        $this->smtp['password'] = env('MAIL_PASSWORD', $this->smtp['password']);
        $this->smtp['timeout'] = (int)env('MAIL_TIMEOUT', $this->smtp['timeout']);
        $this->smtp['keepAlive'] = (bool)env('MAIL_KEEP_ALIVE', $this->smtp['keepAlive']);
        $this->smtp['auth'] = (bool)env('MAIL_AUTH', $this->smtp['auth']);

        // Sendmail
        $this->sendmail['path'] = env('MAIL_SENDMAIL_PATH', $this->sendmail['path']);
        $this->sendmail['flags'] = env('MAIL_SENDMAIL_FLAGS', $this->sendmail['flags']);

        // From
        $this->from['email'] = env('MAIL_FROM_EMAIL', $this->from['email']);
        $this->from['name'] = env('MAIL_FROM_NAME', $this->from['name']);

        // Reply-To
        $this->replyTo = env('MAIL_REPLY_TO', $this->replyTo);

        // Return-Path
        $this->returnPath = env('MAIL_RETURN_PATH', $this->returnPath);

        // Diğer ayarlar
        $this->charset = env('MAIL_CHARSET', $this->charset);
        $this->priority = (int)env('MAIL_PRIORITY', $this->priority);
        $this->wordWrap = (int)env('MAIL_WORD_WRAP', $this->wordWrap);
        $this->templatePath = env('MAIL_TEMPLATE_PATH', $this->templatePath);
        $this->templateCache = (bool)env('MAIL_TEMPLATE_CACHE', $this->templateCache);
        $this->logging = (bool)env('MAIL_LOGGING', $this->logging);
        $this->logPath = env('MAIL_LOG_PATH', $this->logPath);
        $this->queueEnabled = (bool)env('MAIL_QUEUE_ENABLED', $this->queueEnabled);
        $this->queueDriver = env('MAIL_QUEUE_DRIVER', $this->queueDriver);
        $this->batchLimit = (int)env('MAIL_BATCH_LIMIT', $this->batchLimit);
        $this->batchDelay = (int)env('MAIL_BATCH_DELAY', $this->batchDelay);
        $this->validateEmails = (bool)env('MAIL_VALIDATE_EMAILS', $this->validateEmails);
        $this->autoEmbedImages = (bool)env('MAIL_AUTO_EMBED_IMAGES', $this->autoEmbedImages);
        $this->mailer = env('MAIL_MAILER', $this->mailer);
    }
}

