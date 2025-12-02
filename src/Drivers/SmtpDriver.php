<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Drivers;

use Yakupeyisan\CodeIgniter4\Mailer\Config\Mailer as MailerConfig;
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\ConfigurationException;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

class SmtpDriver extends BaseDriver
{
    protected $socket = null;
    protected string $host;
    protected int $port;
    protected string $encryption;
    protected string $username;
    protected string $password;
    protected int $timeout;
    protected bool $keepAlive;
    protected bool $auth;

    public function __construct(MailerConfig $config)
    {
        parent::__construct($config);
        
        $this->host = $this->config->smtp['host'];
        $this->port = $this->config->smtp['port'];
        $this->encryption = $this->config->smtp['encryption'];
        $this->username = $this->config->smtp['username'];
        $this->password = $this->config->smtp['password'];
        $this->timeout = $this->config->smtp['timeout'];
        $this->keepAlive = $this->config->smtp['keepAlive'];
        $this->auth = $this->config->smtp['auth'];
        
        $this->validateConfiguration();
    }

    /**
     * Yapılandırma doğrulaması
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->host)) {
            throw new ConfigurationException("SMTP host belirtilmelidir");
        }
        
        if ($this->auth && (empty($this->username) || empty($this->password))) {
            throw new ConfigurationException("SMTP authentication için username ve password belirtilmelidir");
        }
    }

    /**
     * Email gönder
     */
    public function send(EmailBuilder $builder): bool
    {
        $result = $this->sendWithResult($builder);
        return $result['success'];
    }

    /**
     * Email gönder ve sonucu döndür
     */
    public function sendWithResult(EmailBuilder $builder): array
    {
        try {
            $this->connect();
            $this->authenticate();
            
            $headers = $this->buildHeaders($builder);
            $body = $this->buildBody($builder);
            
            // MAIL FROM
            $from = $builder->getFromEmail();
            $this->sendCommand("MAIL FROM:<{$from}>", 250);
            
            // RCPT TO
            $toAddresses = $this->getToAddresses($builder);
            foreach ($toAddresses as $to) {
                $this->sendCommand("RCPT TO:<{$to}>", 250);
            }
            
            // CC
            foreach ($builder->getCc() as $cc) {
                $this->sendCommand("RCPT TO:<{$cc['email']}>", 250);
            }
            
            // BCC
            foreach ($builder->getBcc() as $bcc) {
                $this->sendCommand("RCPT TO:<{$bcc['email']}>", 250);
            }
            
            // DATA
            $this->sendCommand("DATA", 354);
            
            // Headers gönder
            foreach ($headers as $name => $value) {
                if ($name !== 'To' && $name !== 'Cc' && $name !== 'Bcc') {
                    fwrite($this->socket, "{$name}: {$value}\r\n");
                }
            }
            
            // To, Cc, Bcc gönder
            if (isset($headers['To'])) {
                fwrite($this->socket, "To: {$headers['To']}\r\n");
            }
            if (isset($headers['Cc'])) {
                fwrite($this->socket, "Cc: {$headers['Cc']}\r\n");
            }
            if (isset($headers['Bcc'])) {
                fwrite($this->socket, "Bcc: {$headers['Bcc']}\r\n");
            }
            
            fwrite($this->socket, "\r\n");
            
            // Body gönder
            fwrite($this->socket, $body);
            
            // End of data
            $this->sendCommand("\r\n.", 250);
            
            // QUIT
            if (!$this->keepAlive) {
                $this->sendCommand("QUIT", 221);
                $this->disconnect();
            }
            
            return [
                'success' => true,
                'message' => 'Email başarıyla gönderildi',
            ];
        } catch (\Exception $e) {
            $this->disconnect();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * SMTP bağlantısı
     */
    protected function connect(): void
    {
        $host = $this->host;
        if ($this->encryption === 'ssl') {
            $host = 'ssl://' . $host;
        }
        
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        
        $this->socket = @fsockopen($host, $this->port, $errno, $errstr, $this->timeout, $context);
        
        if (!$this->socket) {
            throw new EmailException("SMTP bağlantısı kurulamadı: {$errstr} ({$errno})");
        }
        
        stream_set_timeout($this->socket, $this->timeout);
        
        $response = $this->getResponse();
        if (substr($response, 0, 3) !== '220') {
            throw new EmailException("SMTP sunucusu yanıt vermedi: {$response}");
        }
        
        // EHLO
        $this->sendCommand("EHLO " . gethostname(), 250);
        
        // STARTTLS
        if ($this->encryption === 'tls') {
            $this->sendCommand("STARTTLS", 220);
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendCommand("EHLO " . gethostname(), 250);
        }
    }

    /**
     * SMTP authentication
     */
    protected function authenticate(): void
    {
        if (!$this->auth) {
            return;
        }
        
        // AUTH LOGIN
        $this->sendCommand("AUTH LOGIN", 334);
        
        // Username
        $this->sendCommand(base64_encode($this->username), 334);
        
        // Password
        $this->sendCommand(base64_encode($this->password), 235);
    }

    /**
     * Komut gönder
     */
    protected function sendCommand(string $command, int $expectedCode): void
    {
        fwrite($this->socket, $command . "\r\n");
        $response = $this->getResponse();
        $code = (int)substr($response, 0, 3);
        
        if ($code !== $expectedCode) {
            throw new EmailException("SMTP komutu başarısız: {$command} - {$response}");
        }
    }

    /**
     * SMTP yanıtını al
     */
    protected function getResponse(): string
    {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return trim($response);
    }

    /**
     * Bağlantıyı kapat
     */
    protected function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Test bağlantısı
     */
    public function test(): bool
    {
        try {
            $this->connect();
            $this->authenticate();
            $this->disconnect();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if (!$this->keepAlive) {
            $this->disconnect();
        }
    }
}

