# Doğrulama Kodu ve Toplantı Linki Örnekleri

Bu dosya doğrulama kodu ve toplantı linki gönderme örneklerini içerir.

## İçindekiler

1. [Doğrulama Kodu Gönderme](#doğrulama-kodu-gönderme)
2. [Doğrulama Linki Gönderme](#doğrulama-linki-gönderme)
3. [Toplantı Daveti Gönderme](#toplantı-daveti-gönderme)
4. [Toplantı Hatırlatması Gönderme](#toplantı-hatırlatması-gönderme)
5. [Özel Template Kullanımı](#özel-template-kullanımı)

## Doğrulama Kodu Gönderme

### Örnek 1: Basit Doğrulama Kodu

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper;

// 6 haneli rastgele kod oluştur
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Email doğrulama kodu gönder
VerificationHelper::sendVerificationCode(
    'user@example.com',
    $code,
    [
        'name' => 'Ahmet Yılmaz',
        'type' => 'email',
        'expires' => '+10 minutes',
    ]
);
```

### Örnek 2: İki Faktörlü Doğrulama (2FA)

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper;

// 2FA kodu oluştur
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

VerificationHelper::sendVerificationCode(
    $user->email,
    $code,
    [
        'name' => $user->name,
        'type' => '2fa',
        'expires' => '+5 minutes',
        'subject' => 'Giriş Doğrulama Kodu',
    ]
);
```

### Örnek 3: Şifre Sıfırlama Kodu

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper;

// Şifre sıfırlama kodu
$code = bin2hex(random_bytes(4)); // 8 karakterlik kod

VerificationHelper::sendVerificationCode(
    $user->email,
    $code,
    [
        'name' => $user->name,
        'type' => 'password_reset',
        'expires' => '+1 hour',
        'subject' => 'Şifre Sıfırlama Kodu',
    ]
);
```

### Örnek 4: Helper Fonksiyonu ile

```php
<?php

// Helper fonksiyonunu kullan
sendVerificationCode(
    'user@example.com',
    '123456',
    [
        'name' => 'Ahmet Yılmaz',
        'expires' => '+15 minutes',
    ]
);
```

### Örnek 5: Controller'da Kullanım

```php
<?php

namespace App\Controllers;

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper;

class AuthController extends BaseController
{
    public function sendVerificationCode()
    {
        $email = $this->request->getPost('email');
        
        // Kodu oluştur ve veritabanına kaydet
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->verificationModel->create([
            'email' => $email,
            'code' => $code,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
            'type' => 'email_verification',
        ]);
        
        // Email gönder
        try {
            VerificationHelper::sendVerificationCode($email, $code, [
                'type' => 'email',
                'expires' => '+10 minutes',
            ]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Doğrulama kodu gönderildi',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kod gönderilemedi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
```

## Doğrulama Linki Gönderme

### Örnek 6: Email Doğrulama Linki

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper;

// Doğrulama token'ı oluştur
$token = bin2hex(random_bytes(32));
$verificationLink = base_url('verify-email/' . $token);

// Linki veritabanına kaydet
$this->userModel->update($user->id, [
    'verification_token' => $token,
    'verification_expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
]);

// Email gönder
VerificationHelper::sendVerificationLink(
    $user->email,
    $verificationLink,
    [
        'name' => $user->name,
        'expires' => '+24 hours',
    ]
);
```

### Örnek 7: Hesap Aktivasyon Linki

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper;

$activationToken = bin2hex(random_bytes(32));
$activationLink = base_url('activate-account/' . $activationToken);

VerificationHelper::sendVerificationLink(
    $user->email,
    $activationLink,
    [
        'name' => $user->name,
        'expires' => '+48 hours',
        'subject' => 'Hesabınızı Aktifleştirin',
    ]
);
```

## Toplantı Daveti Gönderme

### Örnek 8: Basit Toplantı Daveti

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\MeetingHelper;

$meetingLink = 'https://zoom.us/j/1234567890';

MeetingHelper::sendMeetingInvite(
    'participant@example.com',
    $meetingLink,
    [
        'title' => 'Proje Toplantısı',
        'date' => '2024-01-20',
        'time' => '14:00',
        'duration' => '1 saat',
        'organizer' => 'Ahmet Yılmaz',
        'organizer_email' => 'ahmet@example.com',
    ]
);
```

### Örnek 9: Çoklu Katılımcı ile Toplantı

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\MeetingHelper;

$meetingLink = 'https://meet.google.com/abc-defg-hij';

MeetingHelper::sendMeetingInvite(
    [
        'user1@example.com',
        'user2@example.com',
        ['user3@example.com', 'User 3'],
    ],
    $meetingLink,
    [
        'title' => 'Haftalık Ekip Toplantısı',
        'date' => new \DateTime('2024-01-22 10:00', new \DateTimeZone('Europe/Istanbul')),
        'time' => '10:00',
        'duration' => '2 saat',
        'organizer' => 'Proje Yöneticisi',
        'organizer_email' => 'pm@example.com',
        'description' => 'Bu haftanın görevleri ve hedefleri hakkında konuşacağız.',
        'location' => 'Google Meet',
        'timezone' => 'Europe/Istanbul',
    ]
);
```

### Örnek 10: Zoom Toplantısı

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\MeetingHelper;

$zoomLink = 'https://zoom.us/j/1234567890?pwd=abcdefghijklmnop';

MeetingHelper::sendMeetingInvite(
    $participants,
    $zoomLink,
    [
        'title' => 'Müşteri Sunumu',
        'date' => '2024-01-25',
        'time' => '15:30',
        'duration' => '1.5 saat',
        'organizer' => 'Satış Ekibi',
        'organizer_email' => 'sales@example.com',
        'description' => 'Yeni ürün özelliklerini müşteriye sunacağız.',
        'location' => 'Zoom Meeting',
    ]
);
```

### Örnek 11: Microsoft Teams Toplantısı

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\MeetingHelper;

$teamsLink = 'https://teams.microsoft.com/l/meetup-join/...';

MeetingHelper::sendMeetingInvite(
    $teamMembers,
    $teamsLink,
    [
        'title' => 'Teknik Toplantı',
        'date' => '2024-01-18',
        'time' => '11:00',
        'duration' => '45 dakika',
        'organizer' => 'Teknik Lider',
        'organizer_email' => 'tech-lead@example.com',
        'location' => 'Microsoft Teams',
    ]
);
```

## Toplantı Hatırlatması Gönderme

### Örnek 12: Toplantı Hatırlatması

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\MeetingHelper;

$meetingLink = 'https://zoom.us/j/1234567890';

MeetingHelper::sendMeetingReminder(
    $participants,
    $meetingLink,
    [
        'title' => 'Proje Toplantısı',
        'date' => '2024-01-20',
        'time' => '14:00',
        'reminder_time' => '15 dakika',
    ]
);
```

### Örnek 13: Cron Job ile Otomatik Hatırlatma

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Yakupeyisan\CodeIgniter4\Mailer\Helpers\MeetingHelper;

class SendMeetingReminders extends BaseCommand
{
    protected $group = 'Meetings';
    protected $name = 'meetings:remind';
    protected $description = 'Yaklaşan toplantılar için hatırlatma gönder';

    public function run(array $params)
    {
        // 15 dakika içinde başlayacak toplantıları bul
        $meetings = $this->meetingModel
            ->where('date >=', date('Y-m-d H:i:s'))
            ->where('date <=', date('Y-m-d H:i:s', strtotime('+15 minutes')))
            ->where('reminder_sent', false)
            ->findAll();
        
        foreach ($meetings as $meeting) {
            try {
                MeetingHelper::sendMeetingReminder(
                    $meeting->participants,
                    $meeting->link,
                    [
                        'title' => $meeting->title,
                        'date' => $meeting->date,
                        'time' => $meeting->time,
                        'reminder_time' => '15 dakika',
                    ]
                );
                
                // Hatırlatma gönderildi olarak işaretle
                $this->meetingModel->update($meeting->id, ['reminder_sent' => true]);
                
                CLI::write("Hatırlatma gönderildi: {$meeting->title}", 'green');
            } catch (\Exception $e) {
                CLI::error("Hata: {$meeting->title} - {$e->getMessage()}");
            }
        }
    }
}
```

## Özel Template Kullanımı

### Örnek 14: Özel Doğrulama Kodu Template'i

**Template Dosyası: `app/Views/emails/verification-code.html`**

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .code { font-size: 48px; font-weight: bold; color: #667eea; }
    </style>
</head>
<body>
    <h1>Doğrulama Kodunuz</h1>
    <div class="code">{{code}}</div>
    <p>Bu kod {{expires}} tarihine kadar geçerlidir.</p>
</body>
</html>
```

**Kullanım:**

```php
VerificationHelper::sendVerificationCode(
    'user@example.com',
    '123456',
    [
        'template' => 'verification-code',
        'expires' => '+10 minutes',
    ]
);
```

### Örnek 15: Özel Toplantı Template'i

**Template Dosyası: `app/Views/emails/meeting-invite.html`**

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h1>{{title}}</h1>
    <p>Tarih: {{date}}</p>
    <p>Saat: {{time}}</p>
    <p>Süre: {{duration}}</p>
    <a href="{{link}}">Toplantıya Katıl</a>
</body>
</html>
```

**Kullanım:**

```php
MeetingHelper::sendMeetingInvite(
    'user@example.com',
    'https://zoom.us/j/123',
    [
        'template' => 'meeting-invite',
        'title' => 'Toplantı',
        'date' => '2024-01-20',
        'time' => '14:00',
        'duration' => '1 saat',
    ]
);
```

## Gerçek Dünya Senaryoları

### Örnek 16: Kullanıcı Kayıt Sistemi

```php
<?php

namespace App\Controllers;

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper;

class RegisterController extends BaseController
{
    public function register()
    {
        $data = $this->request->getPost();
        
        // Kullanıcı oluştur
        $user = $this->userModel->insert([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'email_verified' => false,
        ]);
        
        // Doğrulama kodu oluştur
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Kodu veritabanına kaydet
        $this->verificationModel->insert([
            'user_id' => $user,
            'code' => $code,
            'type' => 'email_verification',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
        ]);
        
        // Email gönder
        VerificationHelper::sendVerificationCode(
            $data['email'],
            $code,
            [
                'name' => $data['name'],
                'type' => 'email',
                'expires' => '+10 minutes',
            ]
        );
        
        return redirect()->to('/register/success');
    }
    
    public function verifyCode()
    {
        $code = $this->request->getPost('code');
        $email = $this->request->getPost('email');
        
        $verification = $this->verificationModel
            ->where('code', $code)
            ->where('email', $email)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->where('used', false)
            ->first();
        
        if ($verification) {
            // Kullanıcıyı doğrula
            $this->userModel->update($verification->user_id, [
                'email_verified' => true,
            ]);
            
            // Kodu kullanıldı olarak işaretle
            $this->verificationModel->update($verification->id, [
                'used' => true,
            ]);
            
            return redirect()->to('/login')->with('success', 'Email adresiniz doğrulandı!');
        }
        
        return redirect()->back()->with('error', 'Geçersiz veya süresi dolmuş kod!');
    }
}
```

### Örnek 17: Toplantı Yönetim Sistemi

```php
<?php

namespace App\Controllers;

use Yakupeyisan\CodeIgniter4\Mailer\Helpers\MeetingHelper;

class MeetingController extends BaseController
{
    public function create()
    {
        $data = $this->request->getPost();
        
        // Toplantı oluştur
        $meeting = $this->meetingModel->insert([
            'title' => $data['title'],
            'date' => $data['date'],
            'time' => $data['time'],
            'duration' => $data['duration'],
            'link' => $data['link'],
            'organizer_id' => session()->get('user_id'),
            'description' => $data['description'],
        ]);
        
        // Katılımcılara davet gönder
        $participants = explode(',', $data['participants']);
        
        $organizer = $this->userModel->find(session()->get('user_id'));
        
        MeetingHelper::sendMeetingInvite(
            $participants,
            $data['link'],
            [
                'title' => $data['title'],
                'date' => $data['date'],
                'time' => $data['time'],
                'duration' => $data['duration'],
                'organizer' => $organizer->name,
                'organizer_email' => $organizer->email,
                'description' => $data['description'],
                'location' => $data['location'] ?? 'Online',
            ]
        );
        
        return redirect()->to('/meetings')->with('success', 'Toplantı oluşturuldu ve davetler gönderildi!');
    }
}
```

## İpuçları

1. **Güvenlik**: Doğrulama kodlarını hash'leyerek saklayın
2. **Rate Limiting**: Çok fazla kod gönderimini engelleyin
3. **Expiry**: Kodların süresini kısa tutun (5-15 dakika)
4. **Template**: Özel template'ler kullanarak markanıza uygun email'ler gönderin
5. **Queue**: Toplu gönderimlerde queue kullanın
6. **Timezone**: Toplantı tarihlerinde timezone'u doğru ayarlayın

