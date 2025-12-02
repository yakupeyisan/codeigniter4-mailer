<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Drivers;

use Yakupeyisan\CodeIgniter4\Mailer\Config\Mailer as MailerConfig;
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

class MailDriver extends BaseDriver
{
    public function __construct(MailerConfig $config)
    {
        parent::__construct($config);
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
            
            // Mail gönder
            $result = @mail($to, $subject, $body, $headerString);
            
            if (!$result) {
                throw new EmailException("mail() fonksiyonu başarısız oldu");
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
        // mail() fonksiyonu için test yapılamaz
        return function_exists('mail');
    }
}

