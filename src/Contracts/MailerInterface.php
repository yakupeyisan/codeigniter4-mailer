<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Contracts;

interface MailerInterface
{
    /**
     * Email gönderir
     *
     * @param string|array $to
     * @param string $subject
     * @param string $message
     * @param array $options
     * @return bool
     */
    public function send($to, string $subject, string $message, array $options = []): bool;

    /**
     * Email gönderir ve sonucu döndürür
     *
     * @param string|array $to
     * @param string $subject
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendWithResult($to, string $subject, string $message, array $options = []): array;

    /**
     * Test bağlantısı yapar
     *
     * @return bool
     */
    public function test(): bool;
}

