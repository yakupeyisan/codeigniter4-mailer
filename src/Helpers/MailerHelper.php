<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Helpers;

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Mailer;
use Yakupeyisan\CodeIgniter4\Mailer\Templates\EmailTemplate;

if (!function_exists('Yakupeyisan\CodeIgniter4\Mailer\Helpers\mailer')) {
    /**
     * Mailer instance döndür
     */
    function mailer(?string $driver = null): Mailer
    {
        return new Mailer($driver);
    }
}

if (!function_exists('Yakupeyisan\CodeIgniter4\Mailer\Helpers\email')) {
    /**
     * EmailBuilder instance oluştur
     */
    function email(): EmailBuilder
    {
        return new EmailBuilder();
    }
}

if (!function_exists('Yakupeyisan\CodeIgniter4\Mailer\Helpers\template')) {
    /**
     * EmailTemplate instance oluştur
     */
    function template(): EmailTemplate
    {
        return new EmailTemplate();
    }
}

if (!function_exists('Yakupeyisan\CodeIgniter4\Mailer\Helpers\sendVerificationCode')) {
    /**
     * Doğrulama kodu gönder
     */
    function sendVerificationCode(string $email, string $code, array $options = []): bool
    {
        return \Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper::sendVerificationCode($email, $code, $options);
    }
}

if (!function_exists('Yakupeyisan\CodeIgniter4\Mailer\Helpers\sendVerificationLink')) {
    /**
     * Doğrulama linki gönder
     */
    function sendVerificationLink(string $email, string $link, array $options = []): bool
    {
        return \Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper::sendVerificationLink($email, $link, $options);
    }
}

if (!function_exists('Yakupeyisan\CodeIgniter4\Mailer\Helpers\sendMeetingInvite')) {
    /**
     * Toplantı daveti gönder
     */
    function sendMeetingInvite($emails, string $link, array $options = []): bool
    {
        return \Yakupeyisan\CodeIgniter4\Mailer\Helpers\MeetingHelper::sendMeetingInvite($emails, $link, $options);
    }
}

if (!function_exists('Yakupeyisan\CodeIgniter4\Mailer\Helpers\sendMeetingReminder')) {
    /**
     * Toplantı hatırlatması gönder
     */
    function sendMeetingReminder($emails, string $link, array $options = []): bool
    {
        return \Yakupeyisan\CodeIgniter4\Mailer\Helpers\MeetingHelper::sendMeetingReminder($emails, $link, $options);
    }
}

