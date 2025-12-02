<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Drivers;

use Yakupeyisan\CodeIgniter4\Mailer\Config\Mailer as MailerConfig;
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\ConfigurationException;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

class SendmailDriver extends BaseDriver
{
    protected string $sendmailPath;
    protected string $flags;

    public function __construct(MailerConfig $config)
    {
        parent::__construct($config);
        
        $this->sendmailPath = $this->config->sendmail['path'];
        $this->flags = $this->config->sendmail['flags'];
        
        $this->validateConfiguration();
    }

    /**
     * Yapılandırma doğrulaması
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->sendmailPath)) {
            throw new ConfigurationException("Sendmail path belirtilmelidir");
        }
        
        if (!file_exists($this->sendmailPath) && !is_executable($this->sendmailPath)) {
            throw new ConfigurationException("Sendmail path geçerli değil veya çalıştırılabilir değil: {$this->sendmailPath}");
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
            $headers = $this->buildHeaders($builder);
            $body = $this->buildBody($builder);
            
            // Header string oluştur
            $headerString = '';
            foreach ($headers as $name => $value) {
                if ($name !== 'Subject') {
                    $headerString .= "{$name}: {$value}\r\n";
                }
            }
            
            // To adresleri
            $to = $this->formatAddresses($builder->getTo());
            
            // Subject
            $subject = $builder->getSubject() ?? '';
            
            // Sendmail komutu
            $command = escapeshellcmd($this->sendmailPath) . ' ' . $this->flags;
            
            // Process aç
            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            
            $process = proc_open($command, $descriptorspec, $pipes);
            
            if (!is_resource($process)) {
                throw new EmailException("Sendmail process açılamadı");
            }
            
            // Email içeriğini yaz
            fwrite($pipes[0], "To: {$to}\r\n");
            fwrite($pipes[0], "Subject: {$subject}\r\n");
            fwrite($pipes[0], $headerString);
            fwrite($pipes[0], "\r\n");
            fwrite($pipes[0], $body);
            fclose($pipes[0]);
            
            // Çıktıyı oku
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            // Process kapat
            $returnValue = proc_close($process);
            
            if ($returnValue !== 0) {
                throw new EmailException("Sendmail hatası: {$error}");
            }
            
            return [
                'success' => true,
                'message' => 'Email başarıyla gönderildi',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test bağlantısı
     */
    public function test(): bool
    {
        return file_exists($this->sendmailPath) && is_executable($this->sendmailPath);
    }
}

