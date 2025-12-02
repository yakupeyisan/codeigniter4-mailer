# CodeIgniter 4 Mailer Paketi

CodeIgniter 4 için eksiksiz ve güçlü mailer paketi. Tüm mail formatları ve özellikleri ile profesyonel email gönderimi.

## Özellikler

- ✅ **Çoklu Driver Desteği**: SMTP, Mail, Sendmail
- ✅ **HTML ve Plain Text Email**: Her iki formatı da destekler
- ✅ **Multipart Email**: HTML + Text kombinasyonu
- ✅ **Template Sistemi**: Kolay email template'leri
- ✅ **Attachment Desteği**: Dosya ve string içerik ekleme
- ✅ **Inline Images**: Email içine gömülü resimler
- ✅ **CC, BCC, Reply-To**: Tüm email header'ları
- ✅ **Priority Desteği**: Email öncelik seviyeleri
- ✅ **Queue Sistemi**: Asenkron email gönderimi
- ✅ **Batch Gönderim**: Toplu email gönderimi
- ✅ **Email Validation**: Otomatik email doğrulama
- ✅ **Email Logging**: Tüm email'lerin loglanması
- ✅ **Fluent API**: Kolay ve okunabilir kod
- ✅ **UTF-8 Desteği**: Türkçe karakter desteği
- ✅ **Doğrulama Kodu Gönderimi**: Email, SMS, 2FA kodları için hazır template'ler
- ✅ **Doğrulama Linki Gönderimi**: Email aktivasyon linkleri
- ✅ **Toplantı Daveti**: Zoom, Teams, Google Meet linkleri için özel template'ler
- ✅ **Toplantı Hatırlatması**: Otomatik hatırlatma email'leri

## Kurulum

### Composer ile Kurulum

```bash
composer require yakupeyisan/codeigniter4-mailer
```

### Config Dosyasını Kopyalama

Config dosyasını `app/Config/` klasörüne kopyalayın:

```bash
cp vendor/yakupeyisan/codeigniter4-mailer/src/Config/Mailer.php app/Config/Mailer.php
```

### .env Dosyası Ayarları

`.env` dosyanıza aşağıdaki ayarları ekleyin:

```env
# Mail Driver
MAIL_DRIVER=smtp

# SMTP Ayarları
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-password
MAIL_TIMEOUT=30
MAIL_KEEP_ALIVE=false
MAIL_AUTH=true

# Gönderen Bilgileri
MAIL_FROM_EMAIL=noreply@example.com
MAIL_FROM_NAME="My Application"

# Diğer Ayarlar
MAIL_CHARSET=UTF-8
MAIL_PRIORITY=3
MAIL_WORD_WRAP=76
MAIL_TEMPLATE_PATH=app/Views/emails/
MAIL_LOGGING=true
MAIL_LOG_PATH=writable/logs/emails/
MAIL_QUEUE_ENABLED=false
MAIL_QUEUE_DRIVER=database
```

## Kullanım

### Basit Email Gönderimi

```php
use Yakupeyisan\CodeIgniter4\Mailer\Mailer;

$mailer = new Mailer();

// Basit text email
$mailer->send(
    'user@example.com',
    'Hoş Geldiniz',
    'Sitemize hoş geldiniz!'
);

// HTML email
$mailer->send(
    'user@example.com',
    'Hoş Geldiniz',
    '<h1>Hoş Geldiniz!</h1><p>Sitemize hoş geldiniz.</p>',
    ['html' => true]
);
```

### Fluent API ile Email Gönderimi

```php
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();

$email->to('user@example.com', 'Kullanıcı Adı')
      ->cc('manager@example.com')
      ->subject('Önemli Duyuru')
      ->html('<h1>Merhaba!</h1><p>Bu bir HTML email.</p>')
      ->text('Merhaba! Bu bir text email.')
      ->from('noreply@example.com', 'My App')
      ->replyTo('support@example.com')
      ->priority(1)
      ->send();
```

### Attachment ile Email

```php
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();

$email->to('user@example.com')
      ->subject('Dosya Eklentisi')
      ->html('<p>Lütfen ekteki dosyayı inceleyin.</p>')
      ->attach('/path/to/file.pdf')
      ->attach('/path/to/image.jpg', 'resim.jpg', 'image/jpeg')
      ->send();
```

### String İçerik Ekleme

```php
$email->attachData($pdfContent, 'document.pdf', 'application/pdf');
```

### Inline Images (Gömülü Resimler)

```php
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();

$cid = $email->embed('/path/to/logo.png');
// veya
$cid = $email->embedData($imageData, 'image/png');

$email->to('user@example.com')
      ->subject('Logo ile Email')
      ->html("<img src='{$cid}' alt='Logo'>")
      ->send();
```

### Template Kullanımı

#### 1. Template Dosyası Oluştur

`app/Views/emails/welcome.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{subject}}</title>
</head>
<body>
    <h1>Hoş Geldiniz {{name}}!</h1>
    <p>Email adresiniz: {{email}}</p>
    <p>Kayıt tarihiniz: {{date}}</p>
</body>
</html>
```

`app/Views/emails/welcome.txt`:

```
Hoş Geldiniz {{name}}!

Email adresiniz: {{email}}
Kayıt tarihiniz: {{date}}
```

#### 2. Template ile Email Gönder

```php
use Yakupeyisan\CodeIgniter4\Mailer\Templates\EmailTemplate;

$template = new EmailTemplate();

$email = $template->make('welcome', [
    'name' => 'Ahmet Yılmaz',
    'email' => 'ahmet@example.com',
    'date' => date('d.m.Y'),
    'subject' => 'Hoş Geldiniz'
]);

$email->to('ahmet@example.com')
      ->subject('Hoş Geldiniz')
      ->send();
```

### View Renderer ile Template

```php
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();

$email->to('user@example.com')
      ->subject('Hoş Geldiniz')
      ->view('emails/welcome', [
          'name' => 'Ahmet Yılmaz',
          'email' => 'ahmet@example.com'
      ])
      ->send();
```

### Queue ile Email Gönderimi

#### 1. Queue Tablosu Oluştur

Migration dosyası oluşturun:

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailQueueTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'email_data' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'sent', 'failed'],
                'default' => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->createTable('email_queue');
    }

    public function down()
    {
        $this->forge->dropTable('email_queue');
    }
}
```

#### 2. Queue'ya Email Ekle

```php
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Queue\EmailQueue;

$email = new EmailBuilder();
$email->to('user@example.com')
      ->subject('Queue Email')
      ->html('<p>Bu email queue\'dan gönderilecek.</p>');

$queue = new EmailQueue();
$queue->push($email);
```

#### 3. Queue'yu İşle

```php
use Yakupeyisan\CodeIgniter4\Mailer\Queue\EmailQueue;

$queue = new EmailQueue();
$results = $queue->process(10); // 10 email işle

foreach ($results as $result) {
    if ($result['success']) {
        echo "Email gönderildi: {$result['id']}\n";
    } else {
        echo "Email gönderilemedi: {$result['error']}\n";
    }
}
```

### Batch Email Gönderimi

```php
use Yakupeyisan\CodeIgniter4\Mailer\Mailer;
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$mailer = new Mailer();

$emails = [];

foreach ($users as $user) {
    $email = new EmailBuilder();
    $email->to($user['email'])
          ->subject('Toplu Email')
          ->html("<p>Merhaba {$user['name']}!</p>");
    
    $emails[] = $email;
}

$results = $mailer->sendBatch($emails);
```

### Driver Değiştirme

```php
use Yakupeyisan\CodeIgniter4\Mailer\Mailer;

// SMTP
$mailer = new Mailer('smtp');

// Mail
$mailer = new Mailer('mail');

// Sendmail
$mailer = new Mailer('sendmail');

// Veya
$mailer = new Mailer();
$mailer->driver('smtp');
```

### Test Bağlantısı

```php
use Yakupeyisan\CodeIgniter4\Mailer\Mailer;

$mailer = new Mailer('smtp');

if ($mailer->test()) {
    echo "SMTP bağlantısı başarılı!";
} else {
    echo "SMTP bağlantısı başarısız!";
}
```

### Helper Fonksiyonları

```php
use Yakupeyisan\CodeIgniter4\Mailer\Helpers\MailerHelper;

// Mailer instance
$mailer = mailer('smtp');

// EmailBuilder instance
$email = email();
$email->to('user@example.com')
      ->subject('Test')
      ->html('<p>Test</p>')
      ->send();

// Template instance
$template = template();
$email = $template->make('welcome', ['name' => 'Ahmet']);
```

### Custom Headers

```php
$email->header('X-Custom-Header', 'Custom Value')
      ->header('X-Priority', '1')
      ->send();
```

### Email Validation

```php
// Validation açık (varsayılan)
$email->to('user@example.com')->send();

// Validation kapalı
$email->validateEmails(false)
      ->to('invalid-email')
      ->send();
```

### Email Logging

Email'ler otomatik olarak `writable/logs/emails/` klasörüne loglanır. Her gün için ayrı log dosyası oluşturulur.

Log formatı:
```json
{
    "timestamp": "2024-01-15 10:30:00",
    "status": "sent",
    "driver": "smtp",
    "to": [{"email": "user@example.com", "name": null}],
    "subject": "Test Email",
    "from": {"email": "noreply@example.com", "name": "My App"},
    "message": null
}
```

## Email Formatları

### 1. Plain Text Email

```php
$email->to('user@example.com')
      ->subject('Text Email')
      ->text('Bu bir plain text email.')
      ->send();
```

### 2. HTML Email

```php
$email->to('user@example.com')
      ->subject('HTML Email')
      ->html('<h1>Başlık</h1><p>İçerik</p>')
      ->send();
```

### 3. Multipart Email (HTML + Text)

```php
$email->to('user@example.com')
      ->subject('Multipart Email')
      ->html('<h1>HTML Versiyonu</h1>')
      ->text('Text Versiyonu')
      ->send();
```

### 4. Email with Attachments

```php
$email->to('user@example.com')
      ->subject('Attachment Email')
      ->html('<p>Dosya eklentisi var.</p>')
      ->attach('/path/to/file.pdf')
      ->send();
```

### 5. Email with Inline Images

```php
$cid = $email->embed('/path/to/logo.png');
$email->to('user@example.com')
      ->subject('Image Email')
      ->html("<img src='{$cid}' alt='Logo'>")
      ->send();
```

## Yapılandırma

### SMTP Ayarları

```php
// app/Config/Mailer.php

public array $smtp = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls', // tls, ssl, veya boş string
    'username' => 'your-email@gmail.com',
    'password' => 'your-password',
    'timeout' => 30,
    'keepAlive' => false,
    'auth' => true,
];
```

### Sendmail Ayarları

```php
public array $sendmail = [
    'path' => '/usr/sbin/sendmail',
    'flags' => '-bs',
];
```

### Priority Seviyeleri

- `1` - Highest (En Yüksek)
- `2` - High (Yüksek)
- `3` - Normal (Normal) - Varsayılan
- `4` - Low (Düşük)
- `5` - Lowest (En Düşük)

## Hata Yönetimi

```php
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

try {
    $email = new EmailBuilder();
    $email->to('user@example.com')
          ->subject('Test')
          ->html('<p>Test</p>')
          ->send();
} catch (EmailException $e) {
    log_message('error', 'Email gönderilemedi: ' . $e->getMessage());
}
```

## Doğrulama Kodu ve Toplantı Linki

### Doğrulama Kodu Gönderme

```php
use Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper;

// 6 haneli doğrulama kodu oluştur
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Email gönder
VerificationHelper::sendVerificationCode(
    'user@example.com',
    $code,
    [
        'name' => 'Ahmet Yılmaz',
        'type' => 'email', // email, sms, 2fa, password_reset, login
        'expires' => '+10 minutes',
    ]
);
```

### Doğrulama Linki Gönderme

```php
use Yakupeyisan\CodeIgniter4\Mailer\Helpers\VerificationHelper;

$token = bin2hex(random_bytes(32));
$verificationLink = base_url('verify-email/' . $token);

VerificationHelper::sendVerificationLink(
    'user@example.com',
    $verificationLink,
    [
        'name' => 'Ahmet Yılmaz',
        'expires' => '+24 hours',
    ]
);
```

### Toplantı Daveti Gönderme

```php
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
        'description' => 'Proje durumu hakkında konuşacağız.',
        'location' => 'Zoom Meeting',
    ]
);
```

### Toplantı Hatırlatması

```php
use Yakupeyisan\CodeIgniter4\Mailer\Helpers\MeetingHelper;

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

### Helper Fonksiyonları

```php
// Doğrulama kodu
sendVerificationCode('user@example.com', '123456', ['name' => 'Ahmet']);

// Doğrulama linki
sendVerificationLink('user@example.com', 'https://example.com/verify/123');

// Toplantı daveti
sendMeetingInvite('user@example.com', 'https://zoom.us/j/123', ['title' => 'Toplantı']);

// Toplantı hatırlatması
sendMeetingReminder('user@example.com', 'https://zoom.us/j/123', ['title' => 'Toplantı']);
```

Daha fazla örnek için [VERIFICATION_AND_MEETING_EXAMPLES.md](VERIFICATION_AND_MEETING_EXAMPLES.md) dosyasına bakın.

## Örnekler

### Kullanıcı Kayıt Email'i

```php
use Yakupeyisan\CodeIgniter4\Mailer\Templates\EmailTemplate;

$template = new EmailTemplate();

$email = $template->make('user/register', [
    'name' => $user->name,
    'email' => $user->email,
    'activation_link' => base_url('activate/' . $user->token),
]);

$email->to($user->email, $user->name)
      ->subject('Hesabınızı Aktifleştirin')
      ->send();
```

### Şifre Sıfırlama Email'i

```php
$email = new EmailBuilder();

$email->to($user->email, $user->name)
      ->subject('Şifre Sıfırlama')
      ->view('emails/password-reset', [
          'name' => $user->name,
          'reset_link' => base_url('reset-password/' . $token),
          'expires' => date('d.m.Y H:i', strtotime('+1 hour')),
      ])
      ->send();
```

### Toplu Duyuru Email'i

```php
use Yakupeyisan\CodeIgniter4\Mailer\Mailer;
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$mailer = new Mailer();
$emails = [];

foreach ($subscribers as $subscriber) {
    $email = new EmailBuilder();
    $email->to($subscriber['email'])
          ->subject('Yeni Duyuru')
          ->view('emails/announcement', [
              'title' => 'Yeni Ürün',
              'content' => 'Yeni ürünümüzü keşfedin!',
          ]);
    
    $emails[] = $email;
}

$results = $mailer->sendBatch($emails);
```

## Lisans

MIT License

## Destek

Sorularınız için: yakupeyisan@gmail.com

