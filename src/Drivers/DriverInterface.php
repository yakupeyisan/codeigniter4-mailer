<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Drivers;

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

interface DriverInterface
{
    /**
     * Email gönder
     *
     * @param EmailBuilder $builder
     * @return bool
     */
    public function send(EmailBuilder $builder): bool;

    /**
     * Email gönder ve sonucu döndür
     *
     * @param EmailBuilder $builder
     * @return array
     */
    public function sendWithResult(EmailBuilder $builder): array;

    /**
     * Test bağlantısı
     *
     * @return bool
     */
    public function test(): bool;
}

