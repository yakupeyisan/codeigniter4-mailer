<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Drivers;

use Yakupeyisan\CodeIgniter4\Mailer\Config\Mailer as MailerConfig;
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

abstract class BaseDriver implements DriverInterface
{
    protected MailerConfig $config;

    public function __construct(MailerConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Email header'larını oluştur
     */
    protected function buildHeaders(EmailBuilder $builder): array
    {
        $headers = [];
        
        // From
        $from = $builder->getFromEmail();
        $fromName = $builder->getFromName();
        if ($fromName) {
            $headers['From'] = $this->encodeHeader($fromName) . ' <' . $from . '>';
        } else {
            $headers['From'] = $from;
        }
        
        // To
        $to = $this->formatAddresses($builder->getTo());
        if (!empty($to)) {
            $headers['To'] = $to;
        }
        
        // CC
        $cc = $this->formatAddresses($builder->getCc());
        if (!empty($cc)) {
            $headers['Cc'] = $cc;
        }
        
        // BCC
        $bcc = $this->formatAddresses($builder->getBcc());
        if (!empty($bcc)) {
            $headers['Bcc'] = $bcc;
        }
        
        // Reply-To
        $replyTo = $this->formatAddresses($builder->getReplyTo());
        if (!empty($replyTo)) {
            $headers['Reply-To'] = $replyTo;
        }
        
        // Return-Path
        $returnPath = $builder->getReturnPath();
        if ($returnPath) {
            $headers['Return-Path'] = $returnPath;
        }
        
        // Subject
        $headers['Subject'] = $this->encodeHeader($builder->getSubject() ?? '');
        
        // Priority
        $priority = $builder->getPriority() ?? $this->config->priority;
        $headers['X-Priority'] = (string)$priority;
        $priorityMap = [
            1 => 'Highest',
            2 => 'High',
            3 => 'Normal',
            4 => 'Low',
            5 => 'Lowest',
        ];
        $headers['Importance'] = $priorityMap[$priority] ?? 'Normal';
        
        // X-Mailer
        $headers['X-Mailer'] = $this->config->mailer;
        
        // Charset
        $headers['Content-Type'] = $this->getContentType($builder);
        $headers['Content-Transfer-Encoding'] = '8bit';
        
        // Custom headers
        foreach ($builder->getHeaders() as $name => $value) {
            $headers[$name] = $value;
        }
        
        return $headers;
    }

    /**
     * Content-Type belirle
     */
    protected function getContentType(EmailBuilder $builder): string
    {
        $hasHtml = !empty($builder->getHtmlBody());
        $hasText = !empty($builder->getTextBody());
        $hasAttachments = !empty($builder->getAttachments());
        $hasEmbedded = !empty($builder->getEmbeddedImages());
        
        $charset = $builder->getCharset();
        
        if ($hasAttachments || ($hasHtml && $hasEmbedded)) {
            $boundary = $this->generateBoundary();
            return "multipart/mixed; boundary=\"{$boundary}\"";
        }
        
        if ($hasHtml && $hasText) {
            $boundary = $this->generateBoundary();
            return "multipart/alternative; boundary=\"{$boundary}\"";
        }
        
        if ($hasHtml) {
            if ($hasEmbedded) {
                $boundary = $this->generateBoundary();
                return "multipart/related; boundary=\"{$boundary}\"";
            }
            return "text/html; charset={$charset}";
        }
        
        return "text/plain; charset={$charset}";
    }

    /**
     * Email body'yi oluştur
     */
    protected function buildBody(EmailBuilder $builder): string
    {
        $htmlBody = $builder->getHtmlBody();
        $textBody = $builder->getTextBody();
        $attachments = $builder->getAttachments();
        $embeddedImages = $builder->getEmbeddedImages();
        
        $hasHtml = !empty($htmlBody);
        $hasText = !empty($textBody);
        $hasAttachments = !empty($attachments);
        $hasEmbedded = !empty($embeddedImages);
        
        $body = '';
        $boundary = $this->generateBoundary();
        
        // Multipart yapısı
        if ($hasAttachments || ($hasHtml && $hasEmbedded)) {
            // Ana multipart/mixed
            $body .= "--{$boundary}\r\n";
            
            // HTML/Text kısmı
            if ($hasHtml || $hasText) {
                $altBoundary = $this->generateBoundary();
                $body .= "Content-Type: multipart/alternative; boundary=\"{$altBoundary}\"\r\n\r\n";
                
                // Text part
                if ($hasText) {
                    $body .= "--{$altBoundary}\r\n";
                    $body .= "Content-Type: text/plain; charset=" . $builder->getCharset() . "\r\n";
                    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                    $body .= $this->wordWrap($textBody, $builder->getWordWrap()) . "\r\n\r\n";
                }
                
                // HTML part
                if ($hasHtml) {
                    $body .= "--{$altBoundary}\r\n";
                    
                    if ($hasEmbedded) {
                        $relBoundary = $this->generateBoundary();
                        $body .= "Content-Type: multipart/related; boundary=\"{$relBoundary}\"\r\n\r\n";
                        
                        // HTML
                        $body .= "--{$relBoundary}\r\n";
                        $body .= "Content-Type: text/html; charset=" . $builder->getCharset() . "\r\n";
                        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                        $body .= $htmlBody . "\r\n\r\n";
                        
                        // Embedded images
                        foreach ($embeddedImages as $image) {
                            $body .= "--{$relBoundary}\r\n";
                            $body .= "Content-Type: {$image['mime']}\r\n";
                            $body .= "Content-Transfer-Encoding: base64\r\n";
                            $body .= "Content-ID: <{$image['cid']}>\r\n";
                            $body .= "Content-Disposition: inline\r\n\r\n";
                            
                            if ($image['type'] === 'file') {
                                $body .= chunk_split(base64_encode(file_get_contents($image['path']))) . "\r\n";
                            } else {
                                $body .= chunk_split(base64_encode($image['data'])) . "\r\n";
                            }
                        }
                        
                        $body .= "--{$relBoundary}--\r\n";
                    } else {
                        $body .= "Content-Type: text/html; charset=" . $builder->getCharset() . "\r\n";
                        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                        $body .= $htmlBody . "\r\n\r\n";
                    }
                    
                    $body .= "--{$altBoundary}--\r\n";
                }
            } else {
                // Sadece attachment varsa, boş mesaj ekle
                $body .= "Content-Type: text/plain; charset=" . $builder->getCharset() . "\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= "\r\n\r\n";
            }
            
            // Attachments
            foreach ($attachments as $attachment) {
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: {$attachment['mime']}\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-Disposition: attachment; filename=\"" . $this->encodeHeader($attachment['name']) . "\"\r\n\r\n";
                
                if ($attachment['type'] === 'file') {
                    $body .= chunk_split(base64_encode(file_get_contents($attachment['path']))) . "\r\n";
                } else {
                    $body .= chunk_split(base64_encode($attachment['data'])) . "\r\n";
                }
            }
            
            $body .= "--{$boundary}--\r\n";
        } elseif ($hasHtml && $hasText) {
            // Multipart alternative
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=" . $builder->getCharset() . "\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $this->wordWrap($textBody, $builder->getWordWrap()) . "\r\n\r\n";
            
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=" . $builder->getCharset() . "\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $htmlBody . "\r\n\r\n";
            
            $body .= "--{$boundary}--\r\n";
        } elseif ($hasHtml) {
            $body = $htmlBody;
        } else {
            $body = $this->wordWrap($textBody, $builder->getWordWrap());
        }
        
        return $body;
    }

    /**
     * Adres listesini formatla
     */
    protected function formatAddresses(array $addresses): string
    {
        $formatted = [];
        foreach ($addresses as $addr) {
            if (isset($addr['name']) && $addr['name']) {
                $formatted[] = $this->encodeHeader($addr['name']) . ' <' . $addr['email'] . '>';
            } else {
                $formatted[] = $addr['email'];
            }
        }
        return implode(', ', $formatted);
    }

    /**
     * Header encode et
     */
    protected function encodeHeader(string $header): string
    {
        if (preg_match('/[^\x20-\x7E]/', $header)) {
            return '=?UTF-8?B?' . base64_encode($header) . '?=';
        }
        return $header;
    }

    /**
     * Word wrap
     */
    protected function wordWrap(string $text, int $length): string
    {
        return wordwrap($text, $length, "\r\n");
    }

    /**
     * Boundary oluştur
     */
    protected function generateBoundary(): string
    {
        return '----=_Part_' . uniqid() . '_' . time();
    }

    /**
     * To adreslerini array olarak döndür
     */
    protected function getToAddresses(EmailBuilder $builder): array
    {
        $addresses = [];
        foreach ($builder->getTo() as $addr) {
            $addresses[] = $addr['email'];
        }
        return $addresses;
    }
}

