<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Queue;

use Yakupeyisan\CodeIgniter4\Mailer\Config\Mailer as MailerConfig;
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Mailer;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

class EmailQueue
{
    protected MailerConfig $config;
    protected string $driver;

    public function __construct(?MailerConfig $config = null)
    {
        $this->config = $config ?? config('Mailer');
        $this->driver = $this->config->queueDriver;
        
        if (!$this->config->queueEnabled) {
            throw new EmailException("Queue özelliği aktif değil");
        }
    }

    /**
     * Email'i queue'ya ekle
     */
    public function push(EmailBuilder $builder, ?string $driver = null): bool
    {
        $emailData = $builder->toArray();
        $emailData['driver'] = $driver ?? $this->config->defaultDriver;
        
        return match ($this->driver) {
            'database' => $this->pushToDatabase($emailData),
            'file' => $this->pushToFile($emailData),
            'redis' => $this->pushToRedis($emailData),
            default => throw new EmailException("Bilinmeyen queue driver: {$this->driver}"),
        };
    }

    /**
     * Queue'dan email gönder
     */
    public function process(int $limit = 10): array
    {
        $emails = match ($this->driver) {
            'database' => $this->getFromDatabase($limit),
            'file' => $this->getFromFile($limit),
            'redis' => $this->getFromRedis($limit),
            default => throw new EmailException("Bilinmeyen queue driver: {$this->driver}"),
        };
        
        $results = [];
        $mailer = new Mailer();
        
        foreach ($emails as $emailData) {
            try {
                $builder = $this->rebuildEmail($emailData);
                $driver = $emailData['driver'] ?? null;
                
                $result = $mailer->driver($driver)->sendEmail($builder);
                
                $results[] = [
                    'id' => $emailData['id'] ?? null,
                    'success' => $result,
                ];
                
                // Queue'dan sil
                $this->removeFromQueue($emailData);
            } catch (\Exception $e) {
                $results[] = [
                    'id' => $emailData['id'] ?? null,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $results;
    }

    /**
     * Database'e ekle
     */
    protected function pushToDatabase(array $emailData): bool
    {
        $db = \Config\Database::connect();
        
        try {
            $db->table('email_queue')->insert([
                'email_data' => json_encode($emailData, JSON_UNESCAPED_UNICODE),
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'pending',
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Database'den al
     */
    protected function getFromDatabase(int $limit): array
    {
        $db = \Config\Database::connect();
        
        $emails = $db->table('email_queue')
            ->where('status', 'pending')
            ->orderBy('created_at', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
        
        $result = [];
        foreach ($emails as $email) {
            $data = json_decode($email['email_data'], true);
            $data['id'] = $email['id'];
            $result[] = $data;
        }
        
        return $result;
    }

    /**
     * Queue'dan sil
     */
    protected function removeFromQueue(array $emailData): void
    {
        if (!isset($emailData['id'])) {
            return;
        }
        
        match ($this->driver) {
            'database' => $this->removeFromDatabase($emailData),
            'file' => $this->removeFromFile($emailData),
            'redis' => $this->removeFromRedis($emailData),
            default => null,
        };
    }

    /**
     * Database'den sil
     */
    protected function removeFromDatabase(array $emailData): void
    {
        if (!isset($emailData['id'])) {
            return;
        }
        
        $db = \Config\Database::connect();
        $db->table('email_queue')
            ->where('id', $emailData['id'])
            ->update(['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Redis'den sil
     */
    protected function removeFromRedis(array $emailData): void
    {
        // Redis'de zaten rPop ile alındığı için otomatik silinir
    }

    /**
     * File'a ekle
     */
    protected function pushToFile(array $emailData): bool
    {
        $queuePath = WRITEPATH . 'queue/emails/';
        
        if (!is_dir($queuePath)) {
            mkdir($queuePath, 0755, true);
        }
        
        $filename = $queuePath . uniqid('email_', true) . '.json';
        return file_put_contents($filename, json_encode($emailData, JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * File'dan al
     */
    protected function getFromFile(int $limit): array
    {
        $queuePath = WRITEPATH . 'queue/emails/';
        
        if (!is_dir($queuePath)) {
            return [];
        }
        
        $files = glob($queuePath . '*.json');
        $emails = [];
        
        foreach (array_slice($files, 0, $limit) as $file) {
            $data = json_decode(file_get_contents($file), true);
            $data['id'] = $file;
            $emails[] = $data;
        }
        
        return $emails;
    }

    /**
     * File'dan sil
     */
    protected function removeFromFile(array $emailData): void
    {
        if (isset($emailData['id']) && file_exists($emailData['id'])) {
            unlink($emailData['id']);
        }
    }

    /**
     * Redis'e ekle
     */
    protected function pushToRedis(array $emailData): bool
    {
        if (!extension_loaded('redis')) {
            throw new EmailException("Redis extension yüklü değil");
        }
        
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        
        $key = 'email_queue:' . uniqid();
        return $redis->lPush('email_queue', json_encode($emailData, JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * Redis'den al
     */
    protected function getFromRedis(int $limit): array
    {
        if (!extension_loaded('redis')) {
            throw new EmailException("Redis extension yüklü değil");
        }
        
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        
        $emails = [];
        for ($i = 0; $i < $limit; $i++) {
            $data = $redis->rPop('email_queue');
            if ($data) {
                $emails[] = json_decode($data, true);
            } else {
                break;
            }
        }
        
        return $emails;
    }

    /**
     * EmailBuilder'ı yeniden oluştur
     */
    protected function rebuildEmail(array $emailData): EmailBuilder
    {
        $builder = new EmailBuilder($this->config);
        
        // To
        foreach ($emailData['to'] ?? [] as $to) {
            $builder->to($to['email'], $to['name'] ?? null);
        }
        
        // CC
        foreach ($emailData['cc'] ?? [] as $cc) {
            $builder->cc($cc['email'], $cc['name'] ?? null);
        }
        
        // BCC
        foreach ($emailData['bcc'] ?? [] as $bcc) {
            $builder->bcc($bcc['email'], $bcc['name'] ?? null);
        }
        
        // From
        if (isset($emailData['from'])) {
            $builder->from($emailData['from']['email'], $emailData['from']['name'] ?? null);
        }
        
        // Reply-To
        foreach ($emailData['replyTo'] ?? [] as $replyTo) {
            $builder->replyTo($replyTo['email'], $replyTo['name'] ?? null);
        }
        
        // Return-Path
        if (isset($emailData['returnPath'])) {
            $builder->returnPath($emailData['returnPath']);
        }
        
        // Subject
        if (isset($emailData['subject'])) {
            $builder->subject($emailData['subject']);
        }
        
        // HTML
        if (isset($emailData['htmlBody'])) {
            $builder->html($emailData['htmlBody']);
        }
        
        // Text
        if (isset($emailData['textBody'])) {
            $builder->text($emailData['textBody']);
        }
        
        // Attachments (sadece data tipi desteklenir, file path'ler kaybolur)
        foreach ($emailData['attachments'] ?? [] as $attachment) {
            if ($attachment['type'] === 'data') {
                $builder->attachData($attachment['data'], $attachment['name'], $attachment['mime']);
            }
        }
        
        // Priority
        if (isset($emailData['priority'])) {
            $builder->priority($emailData['priority']);
        }
        
        // Charset
        if (isset($emailData['charset'])) {
            $builder->charset($emailData['charset']);
        }
        
        return $builder;
    }
}

