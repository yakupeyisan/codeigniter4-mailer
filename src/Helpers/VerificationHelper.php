<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Helpers;

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Templates\EmailTemplate;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

class VerificationHelper
{
    /**
     * Doğrulama kodu gönder
     *
     * @param string $email Alıcı email adresi
     * @param string $code Doğrulama kodu
     * @param array $options Ek seçenekler (name, expires, type, etc.)
     * @return bool
     */
    public static function sendVerificationCode(string $email, string $code, array $options = []): bool
    {
        $name = $options['name'] ?? null;
        $expires = $options['expires'] ?? '+10 minutes';
        $type = $options['type'] ?? 'email'; // email, sms, 2fa, password_reset, etc.
        $subject = $options['subject'] ?? self::getDefaultSubject($type);
        $template = $options['template'] ?? 'verification-code';
        
        $expiresTime = is_string($expires) ? date('d.m.Y H:i', strtotime($expires)) : $expires;
        
        try {
            $emailBuilder = new EmailBuilder();
            
            // Template kullan
            if (file_exists(APPPATH . 'Views/emails/' . $template . '.html')) {
                $emailTemplate = new EmailTemplate();
                $emailBuilder = $emailTemplate->make($template, [
                    'code' => $code,
                    'name' => $name,
                    'email' => $email,
                    'expires' => $expiresTime,
                    'type' => $type,
                ]);
            } else {
                // Varsayılan template
                $emailBuilder->html(self::getDefaultHtmlTemplate($code, $name, $expiresTime, $type));
                $emailBuilder->text(self::getDefaultTextTemplate($code, $name, $expiresTime, $type));
            }
            
            $emailBuilder->to($email, $name)
                        ->subject($subject)
                        ->priority(1); // Yüksek öncelik
            
            return $emailBuilder->send();
        } catch (\Exception $e) {
            throw new EmailException("Doğrulama kodu gönderilemedi: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Email doğrulama linki gönder
     *
     * @param string $email Alıcı email adresi
     * @param string $verificationLink Doğrulama linki
     * @param array $options Ek seçenekler
     * @return bool
     */
    public static function sendVerificationLink(string $email, string $verificationLink, array $options = []): bool
    {
        $name = $options['name'] ?? null;
        $expires = $options['expires'] ?? '+24 hours';
        $subject = $options['subject'] ?? 'Email Adresinizi Doğrulayın';
        $template = $options['template'] ?? 'verification-link';
        
        $expiresTime = is_string($expires) ? date('d.m.Y H:i', strtotime($expires)) : $expires;
        
        try {
            $emailBuilder = new EmailBuilder();
            
            // Template kullan
            if (file_exists(APPPATH . 'Views/emails/' . $template . '.html')) {
                $emailTemplate = new EmailTemplate();
                $emailBuilder = $emailTemplate->make($template, [
                    'link' => $verificationLink,
                    'name' => $name,
                    'email' => $email,
                    'expires' => $expiresTime,
                ]);
            } else {
                // Varsayılan template
                $emailBuilder->html(self::getDefaultLinkHtmlTemplate($verificationLink, $name, $expiresTime));
                $emailBuilder->text(self::getDefaultLinkTextTemplate($verificationLink, $name, $expiresTime));
            }
            
            $emailBuilder->to($email, $name)
                        ->subject($subject)
                        ->priority(1);
            
            return $emailBuilder->send();
        } catch (\Exception $e) {
            throw new EmailException("Doğrulama linki gönderilemedi: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Varsayılan konu başlığı
     */
    protected static function getDefaultSubject(string $type): string
    {
        return match ($type) {
            'email' => 'Email Doğrulama Kodu',
            'sms' => 'SMS Doğrulama Kodu',
            '2fa' => 'İki Faktörlü Doğrulama Kodu',
            'password_reset' => 'Şifre Sıfırlama Kodu',
            'login' => 'Giriş Doğrulama Kodu',
            default => 'Doğrulama Kodu',
        };
    }

    /**
     * Varsayılan HTML template
     */
    protected static function getDefaultHtmlTemplate(string $code, ?string $name, string $expires, string $type): string
    {
        $greeting = $name ? "Merhaba {$name}," : "Merhaba,";
        $typeText = match ($type) {
            'email' => 'email adresinizi doğrulamak',
            'sms' => 'telefon numaranızı doğrulamak',
            '2fa' => 'giriş yapmak',
            'password_reset' => 'şifrenizi sıfırlamak',
            'login' => 'giriş yapmak',
            default => 'işleminizi tamamlamak',
        };
        
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
                .code-box { background: white; border: 2px dashed #667eea; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 8px; font-family: 'Courier New', monospace; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Doğrulama Kodu</h1>
                </div>
                <div class='content'>
                    <p>{$greeting}</p>
                    <p>{$typeText} için doğrulama kodunuz:</p>
                    <div class='code-box'>
                        <div class='code'>{$code}</div>
                    </div>
                    <div class='warning'>
                        <strong>⚠️ Güvenlik Uyarısı:</strong><br>
                        Bu kodu kimseyle paylaşmayın. Kod {$expires} tarihine kadar geçerlidir.
                    </div>
                    <p>Eğer bu işlemi siz yapmadıysanız, bu email'i görmezden gelebilirsiniz.</p>
                </div>
                <div class='footer'>
                    <p>Bu otomatik bir email'dir. Lütfen yanıtlamayın.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Varsayılan text template
     */
    protected static function getDefaultTextTemplate(string $code, ?string $name, string $expires, string $type): string
    {
        $greeting = $name ? "Merhaba {$name}," : "Merhaba,";
        $typeText = match ($type) {
            'email' => 'email adresinizi doğrulamak',
            'sms' => 'telefon numaranızı doğrulamak',
            '2fa' => 'giriş yapmak',
            'password_reset' => 'şifrenizi sıfırlamak',
            'login' => 'giriş yapmak',
            default => 'işleminizi tamamlamak',
        };
        
        return "
{$greeting}

{$typeText} için doğrulama kodunuz:

{$code}

GÜVENLİK UYARISI:
Bu kodu kimseyle paylaşmayın. Kod {$expires} tarihine kadar geçerlidir.

Eğer bu işlemi siz yapmadıysanız, bu email'i görmezden gelebilirsiniz.

---
Bu otomatik bir email'dir. Lütfen yanıtlamayın.
        ";
    }

    /**
     * Varsayılan link HTML template
     */
    protected static function getDefaultLinkHtmlTemplate(string $link, ?string $name, string $expires): string
    {
        $greeting = $name ? "Merhaba {$name}," : "Merhaba,";
        
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
                .button { display: inline-block; background: #667eea; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Email Doğrulama</h1>
                </div>
                <div class='content'>
                    <p>{$greeting}</p>
                    <p>Email adresinizi doğrulamak için aşağıdaki butona tıklayın:</p>
                    <div style='text-align: center;'>
                        <a href='{$link}' class='button'>Email Adresimi Doğrula</a>
                    </div>
                    <p>Veya aşağıdaki linki tarayıcınıza kopyalayın:</p>
                    <p style='word-break: break-all; color: #667eea;'>{$link}</p>
                    <div class='warning'>
                        <strong>⚠️ Güvenlik Uyarısı:</strong><br>
                        Bu link {$expires} tarihine kadar geçerlidir. Linki kimseyle paylaşmayın.
                    </div>
                    <p>Eğer bu işlemi siz yapmadıysanız, bu email'i görmezden gelebilirsiniz.</p>
                </div>
                <div class='footer'>
                    <p>Bu otomatik bir email'dir. Lütfen yanıtlamayın.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Varsayılan link text template
     */
    protected static function getDefaultLinkTextTemplate(string $link, ?string $name, string $expires): string
    {
        $greeting = $name ? "Merhaba {$name}," : "Merhaba,";
        
        return "
{$greeting}

Email adresinizi doğrulamak için aşağıdaki linke tıklayın:

{$link}

GÜVENLİK UYARISI:
Bu link {$expires} tarihine kadar geçerlidir. Linki kimseyle paylaşmayın.

Eğer bu işlemi siz yapmadıysanız, bu email'i görmezden gelebilirsiniz.

---
Bu otomatik bir email'dir. Lütfen yanıtlamayın.
        ";
    }
}

