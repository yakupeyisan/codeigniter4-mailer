<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Helpers;

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Templates\EmailTemplate;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

class MeetingHelper
{
    /**
     * Toplantı daveti gönder
     *
     * @param string|array $emails Alıcı email adresleri
     * @param string $meetingLink Toplantı linki (Zoom, Teams, Google Meet, vb.)
     * @param array $options Toplantı bilgileri
     * @return bool
     */
    public static function sendMeetingInvite($emails, string $meetingLink, array $options = []): bool
    {
        $title = $options['title'] ?? 'Toplantı Daveti';
        $date = $options['date'] ?? null;
        $time = $options['time'] ?? null;
        $duration = $options['duration'] ?? '1 saat';
        $organizer = $options['organizer'] ?? null;
        $organizerEmail = $options['organizer_email'] ?? null;
        $description = $options['description'] ?? null;
        $location = $options['location'] ?? null;
        $timezone = $options['timezone'] ?? 'Europe/Istanbul';
        $template = $options['template'] ?? 'meeting-invite';
        $subject = $options['subject'] ?? "Toplantı Daveti: {$title}";
        
        // Tarih formatla
        $formattedDate = $date ? self::formatDate($date, $timezone) : null;
        $formattedTime = $time ? self::formatTime($time, $timezone) : null;
        
        try {
            $emailBuilder = new EmailBuilder();
            
            // Template kullan
            if (file_exists(APPPATH . 'Views/emails/' . $template . '.html')) {
                $emailTemplate = new EmailTemplate();
                $emailBuilder = $emailTemplate->make($template, [
                    'title' => $title,
                    'link' => $meetingLink,
                    'date' => $formattedDate,
                    'time' => $formattedTime,
                    'duration' => $duration,
                    'organizer' => $organizer,
                    'organizer_email' => $organizerEmail,
                    'description' => $description,
                    'location' => $location,
                ]);
            } else {
                // Varsayılan template
                $emailBuilder->html(self::getDefaultHtmlTemplate(
                    $title,
                    $meetingLink,
                    $formattedDate,
                    $formattedTime,
                    $duration,
                    $organizer,
                    $organizerEmail,
                    $description,
                    $location
                ));
                $emailBuilder->text(self::getDefaultTextTemplate(
                    $title,
                    $meetingLink,
                    $formattedDate,
                    $formattedTime,
                    $duration,
                    $organizer,
                    $organizerEmail,
                    $description,
                    $location
                ));
            }
            
            // Organizer'dan gönder
            if ($organizerEmail) {
                $emailBuilder->from($organizerEmail, $organizer ?? 'Toplantı Organizatörü');
            }
            
            // Reply-To ayarla
            if ($organizerEmail) {
                $emailBuilder->replyTo($organizerEmail, $organizer);
            }
            
            $emailBuilder->to($emails)
                        ->subject($subject)
                        ->priority(2); // Yüksek öncelik
            
            return $emailBuilder->send();
        } catch (\Exception $e) {
            throw new EmailException("Toplantı daveti gönderilemedi: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Toplantı hatırlatması gönder
     *
     * @param string|array $emails Alıcı email adresleri
     * @param string $meetingLink Toplantı linki
     * @param array $options Toplantı bilgileri
     * @return bool
     */
    public static function sendMeetingReminder($emails, string $meetingLink, array $options = []): bool
    {
        $title = $options['title'] ?? 'Toplantı';
        $date = $options['date'] ?? null;
        $time = $options['time'] ?? null;
        $reminderTime = $options['reminder_time'] ?? '15 dakika';
        $timezone = $options['timezone'] ?? 'Europe/Istanbul';
        
        $formattedDate = $date ? self::formatDate($date, $timezone) : null;
        $formattedTime = $time ? self::formatTime($time, $timezone) : null;
        
        try {
            $emailBuilder = new EmailBuilder();
            
            $emailBuilder->html(self::getReminderHtmlTemplate(
                $title,
                $meetingLink,
                $formattedDate,
                $formattedTime,
                $reminderTime
            ));
            $emailBuilder->text(self::getReminderTextTemplate(
                $title,
                $meetingLink,
                $formattedDate,
                $formattedTime,
                $reminderTime
            ));
            
            $emailBuilder->to($emails)
                        ->subject("Hatırlatma: {$title} - {$reminderTime} sonra")
                        ->priority(1); // En yüksek öncelik
            
            return $emailBuilder->send();
        } catch (\Exception $e) {
            throw new EmailException("Toplantı hatırlatması gönderilemedi: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Tarih formatla
     */
    protected static function formatDate($date, string $timezone): string
    {
        if (is_string($date)) {
            $date = new \DateTime($date, new \DateTimeZone($timezone));
        }
        
        if ($date instanceof \DateTime) {
            $date->setTimezone(new \DateTimeZone($timezone));
            return $date->format('d.m.Y');
        }
        
        return (string)$date;
    }

    /**
     * Saat formatla
     */
    protected static function formatTime($time, string $timezone): string
    {
        if (is_string($time)) {
            $time = new \DateTime($time, new \DateTimeZone($timezone));
        }
        
        if ($time instanceof \DateTime) {
            $time->setTimezone(new \DateTimeZone($timezone));
            return $time->format('H:i');
        }
        
        return (string)$time;
    }

    /**
     * Varsayılan HTML template
     */
    protected static function getDefaultHtmlTemplate(
        string $title,
        string $link,
        ?string $date,
        ?string $time,
        string $duration,
        ?string $organizer,
        ?string $organizerEmail,
        ?string $description,
        ?string $location
    ): string {
        $dateTimeInfo = '';
        if ($date && $time) {
            $dateTimeInfo = "<p><strong>📅 Tarih:</strong> {$date}</p><p><strong>🕐 Saat:</strong> {$time}</p>";
        } elseif ($date) {
            $dateTimeInfo = "<p><strong>📅 Tarih:</strong> {$date}</p>";
        }
        
        $durationInfo = $duration ? "<p><strong>⏱️ Süre:</strong> {$duration}</p>" : '';
        $locationInfo = $location ? "<p><strong>📍 Konum:</strong> {$location}</p>" : '';
        $organizerInfo = $organizer ? "<p><strong>👤 Organizatör:</strong> {$organizer}" . ($organizerEmail ? " ({$organizerEmail})" : '') . "</p>" : '';
        $descriptionInfo = $description ? "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0;'><p><strong>📝 Açıklama:</strong></p><p>{$description}</p></div>" : '';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #667eea; }
                .button { display: inline-block; background: #667eea; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📅 Toplantı Daveti</h1>
                </div>
                <div class='content'>
                    <h2>{$title}</h2>
                    <div class='info-box'>
                        {$dateTimeInfo}
                        {$durationInfo}
                        {$locationInfo}
                        {$organizerInfo}
                    </div>
                    {$descriptionInfo}
                    <div style='text-align: center;'>
                        <a href='{$link}' class='button'>Toplantıya Katıl</a>
                    </div>
                    <p style='word-break: break-all; color: #667eea; text-align: center;'>{$link}</p>
                </div>
                <div class='footer'>
                    <p>Bu otomatik bir email'dir. Sorularınız için organizatör ile iletişime geçebilirsiniz.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Varsayılan text template
     */
    protected static function getDefaultTextTemplate(
        string $title,
        string $link,
        ?string $date,
        ?string $time,
        string $duration,
        ?string $organizer,
        ?string $organizerEmail,
        ?string $description,
        ?string $location
    ): string {
        $dateTimeInfo = '';
        if ($date && $time) {
            $dateTimeInfo = "Tarih: {$date}\nSaat: {$time}\n";
        } elseif ($date) {
            $dateTimeInfo = "Tarih: {$date}\n";
        }
        
        $durationInfo = $duration ? "Süre: {$duration}\n" : '';
        $locationInfo = $location ? "Konum: {$location}\n" : '';
        $organizerInfo = $organizer ? "Organizatör: {$organizer}" . ($organizerEmail ? " ({$organizerEmail})" : '') . "\n" : '';
        $descriptionInfo = $description ? "\nAçıklama:\n{$description}\n" : '';
        
        return "
TOPLANTI DAVETİ

{$title}

{$dateTimeInfo}{$durationInfo}{$locationInfo}{$organizerInfo}{$descriptionInfo}

Toplantıya katılmak için link:
{$link}

---
Bu otomatik bir email'dir. Sorularınız için organizatör ile iletişime geçebilirsiniz.
        ";
    }

    /**
     * Hatırlatma HTML template
     */
    protected static function getReminderHtmlTemplate(
        string $title,
        string $link,
        ?string $date,
        ?string $time,
        string $reminderTime
    ): string {
        $dateTimeInfo = '';
        if ($date && $time) {
            $dateTimeInfo = "<p><strong>📅 Tarih:</strong> {$date}</p><p><strong>🕐 Saat:</strong> {$time}</p>";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .reminder-box { background: #fff3cd; border: 2px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }
                .button { display: inline-block; background: #f5576c; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⏰ Toplantı Hatırlatması</h1>
                </div>
                <div class='content'>
                    <div class='reminder-box'>
                        <h2>{$title}</h2>
                        {$dateTimeInfo}
                        <p style='font-size: 18px; font-weight: bold; color: #f5576c;'>Toplantı {$reminderTime} sonra başlayacak!</p>
                    </div>
                    <div style='text-align: center;'>
                        <a href='{$link}' class='button'>Toplantıya Katıl</a>
                    </div>
                    <p style='word-break: break-all; color: #667eea; text-align: center;'>{$link}</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Hatırlatma text template
     */
    protected static function getReminderTextTemplate(
        string $title,
        string $link,
        ?string $date,
        ?string $time,
        string $reminderTime
    ): string {
        $dateTimeInfo = '';
        if ($date && $time) {
            $dateTimeInfo = "Tarih: {$date}\nSaat: {$time}\n";
        }
        
        return "
TOPLANTI HATIRLATMASI

{$title}

{$dateTimeInfo}
Toplantı {$reminderTime} sonra başlayacak!

Toplantıya katılmak için link:
{$link}
        ";
    }
}

