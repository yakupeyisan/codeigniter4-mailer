# CodeIgniter 4 Mailer - Örnekler

Bu dosya CodeIgniter 4 Mailer paketinin kullanım örneklerini içerir.

## İçindekiler

1. [Basit Kullanımlar](#basit-kullanımlar)
2. [Gelişmiş Özellikler](#gelişmiş-özellikler)
3. [Template Örnekleri](#template-örnekleri)
4. [Queue Örnekleri](#queue-örnekleri)
5. [Gerçek Dünya Senaryoları](#gerçek-dünya-senaryoları)

## Basit Kullanımlar

### Örnek 1: Basit Text Email

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Mailer;

$mailer = new Mailer();
$mailer->send(
    'user@example.com',
    'Hoş Geldiniz',
    'Sitemize hoş geldiniz!'
);
```

### Örnek 2: HTML Email

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Mailer;

$mailer = new Mailer();
$mailer->send(
    'user@example.com',
    'Hoş Geldiniz',
    '<h1>Hoş Geldiniz!</h1><p>Sitemize hoş geldiniz.</p>',
    ['html' => true]
);
```

### Örnek 3: Fluent API ile Email

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();
$email->to('user@example.com', 'Kullanıcı Adı')
      ->subject('Önemli Duyuru')
      ->html('<h1>Merhaba!</h1><p>Bu bir HTML email.</p>')
      ->text('Merhaba! Bu bir text email.')
      ->send();
```

### Örnek 4: Çoklu Alıcı

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();
$email->to([
          'user1@example.com',
          'user2@example.com',
          ['user3@example.com', 'User 3'],
      ])
      ->subject('Toplu Email')
      ->html('<p>Bu email birden fazla kişiye gönderiliyor.</p>')
      ->send();
```

### Örnek 5: CC ve BCC

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();
$email->to('user@example.com')
      ->cc('manager@example.com', 'Yönetici')
      ->bcc('admin@example.com')
      ->subject('Rapor')
      ->html('<p>Rapor ektedir.</p>')
      ->send();
```

## Gelişmiş Özellikler

### Örnek 6: Dosya Eklentisi

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();
$email->to('user@example.com')
      ->subject('Dosya Eklentisi')
      ->html('<p>Lütfen ekteki dosyayı inceleyin.</p>')
      ->attach('/path/to/document.pdf')
      ->attach('/path/to/image.jpg', 'resim.jpg', 'image/jpeg')
      ->send();
```

### Örnek 7: String İçerik Ekleme

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

// PDF içeriğini oluştur
$pdfContent = generatePdfContent();

$email = new EmailBuilder();
$email->to('user@example.com')
      ->subject('PDF Eklentisi')
      ->html('<p>PDF dosyası ektedir.</p>')
      ->attachData($pdfContent, 'document.pdf', 'application/pdf')
      ->send();
```

### Örnek 8: Inline Images (Gömülü Resimler)

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();

// Logo'yu göm
$logoCid = $email->embed('/path/to/logo.png');

// Banner'ı göm
$bannerCid = $email->embedData($bannerImageData, 'image/png');

$email->to('user@example.com')
      ->subject('Görsel Email')
      ->html("
          <div>
              <img src='{$logoCid}' alt='Logo' style='max-width: 200px;'>
              <h1>Hoş Geldiniz!</h1>
              <img src='{$bannerCid}' alt='Banner' style='max-width: 100%;'>
              <p>Email içeriği burada.</p>
          </div>
      ")
      ->send();
```

### Örnek 9: Priority Ayarlama

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();
$email->to('user@example.com')
      ->subject('Acil Email')
      ->html('<p>Bu çok önemli bir email.</p>')
      ->priority(1) // Highest priority
      ->send();
```

### Örnek 10: Custom Headers

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();
$email->to('user@example.com')
      ->subject('Custom Header Email')
      ->html('<p>Bu email custom header içeriyor.</p>')
      ->header('X-Custom-Header', 'Custom Value')
      ->header('X-Priority', '1')
      ->header('X-Campaign-ID', '12345')
      ->send();
```

### Örnek 11: Reply-To ve Return-Path

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();
$email->to('user@example.com')
      ->subject('Destek Email')
      ->html('<p>Lütfen yanıtlamak için reply-to adresini kullanın.</p>')
      ->from('noreply@example.com', 'My App')
      ->replyTo('support@example.com', 'Destek Ekibi')
      ->returnPath('bounce@example.com')
      ->send();
```

## Template Örnekleri

### Örnek 12: Basit Template

**Template Dosyası: `app/Views/emails/welcome.html`**

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hoş Geldiniz</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { padding: 20px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Hoş Geldiniz {{name}}!</h1>
        </div>
        <div class="content">
            <p>Merhaba {{name}},</p>
            <p>Sitemize kayıt olduğunuz için teşekkür ederiz.</p>
            <p>Email adresiniz: <strong>{{email}}</strong></p>
            <p>Kayıt tarihiniz: <strong>{{date}}</strong></p>
            <p>
                <a href="{{activation_link}}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    Hesabınızı Aktifleştirin
                </a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; 2024 My Application. Tüm hakları saklıdır.</p>
        </div>
    </div>
</body>
</html>
```

**Kullanım:**

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Templates\EmailTemplate;

$template = new EmailTemplate();

$email = $template->make('welcome', [
    'name' => 'Ahmet Yılmaz',
    'email' => 'ahmet@example.com',
    'date' => date('d.m.Y H:i'),
    'activation_link' => base_url('activate/' . $token),
]);

$email->to('ahmet@example.com', 'Ahmet Yılmaz')
      ->subject('Hoş Geldiniz!')
      ->send();
```

### Örnek 13: View Renderer ile Template

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

$email = new EmailBuilder();
$email->to('user@example.com')
      ->subject('Hoş Geldiniz')
      ->view('emails/welcome', [
          'name' => 'Ahmet Yılmaz',
          'email' => 'ahmet@example.com',
          'activation_link' => base_url('activate/' . $token),
      ])
      ->send();
```

### Örnek 14: Şifre Sıfırlama Template

**Template Dosyası: `app/Views/emails/password-reset.html`**

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Şifre Sıfırlama</title>
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1>Şifre Sıfırlama</h1>
        <p>Merhaba {{name}},</p>
        <p>Şifrenizi sıfırlamak için aşağıdaki linke tıklayın:</p>
        <p>
            <a href="{{reset_link}}" style="background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                Şifremi Sıfırla
            </a>
        </p>
        <p>Bu link {{expires}} tarihine kadar geçerlidir.</p>
        <p>Eğer bu işlemi siz yapmadıysanız, bu email'i görmezden gelebilirsiniz.</p>
    </div>
</body>
</html>
```

**Kullanım:**

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Templates\EmailTemplate;

$template = new EmailTemplate();

$token = bin2hex(random_bytes(32));
$resetLink = base_url('reset-password/' . $token);

$email = $template->make('password-reset', [
    'name' => $user->name,
    'reset_link' => $resetLink,
    'expires' => date('d.m.Y H:i', strtotime('+1 hour')),
]);

$email->to($user->email, $user->name)
      ->subject('Şifre Sıfırlama')
      ->send();
```

## Queue Örnekleri

### Örnek 15: Queue'ya Email Ekleme

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Queue\EmailQueue;

$email = new EmailBuilder();
$email->to('user@example.com')
      ->subject('Queue Email')
      ->html('<p>Bu email queue\'dan gönderilecek.</p>');

$queue = new EmailQueue();
$queue->push($email);
```

### Örnek 16: Queue İşleme

```php
<?php

use Yakupeyisan\CodeIgniter4\Mailer\Queue\EmailQueue;

$queue = new EmailQueue();
$results = $queue->process(10); // 10 email işle

foreach ($results as $result) {
    if ($result['success']) {
        log_message('info', "Email gönderildi: {$result['id']}");
    } else {
        log_message('error', "Email gönderilemedi: {$result['error']}");
    }
}
```

### Örnek 17: Cron Job ile Queue İşleme

**Command: `app/Commands/ProcessEmailQueue.php`**

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Yakupeyisan\CodeIgniter4\Mailer\Queue\EmailQueue;

class ProcessEmailQueue extends BaseCommand
{
    protected $group = 'Email';
    protected $name = 'email:process';
    protected $description = 'Email queue\'yu işle';

    public function run(array $params)
    {
        $limit = $params[0] ?? 10;
        
        CLI::write("Email queue işleniyor... (Limit: {$limit})", 'yellow');
        
        $queue = new EmailQueue();
        $results = $queue->process($limit);
        
        $success = 0;
        $failed = 0;
        
        foreach ($results as $result) {
            if ($result['success']) {
                $success++;
            } else {
                $failed++;
                CLI::error("Email gönderilemedi: {$result['error']}");
            }
        }
        
        CLI::write("Başarılı: {$success}, Başarısız: {$failed}", 'green');
    }
}
```

**Cron Job:**

```bash
# Her 5 dakikada bir çalıştır
*/5 * * * * cd /path/to/project && php spark email:process 50
```

## Gerçek Dünya Senaryoları

### Örnek 18: Kullanıcı Kayıt Email'i

```php
<?php

namespace App\Controllers;

use Yakupeyisan\CodeIgniter4\Mailer\Templates\EmailTemplate;

class RegisterController extends BaseController
{
    public function register()
    {
        // Kullanıcı kayıt işlemleri...
        $user = $this->userModel->create($data);
        
        // Aktivasyon token oluştur
        $token = bin2hex(random_bytes(32));
        $this->userModel->update($user->id, ['activation_token' => $token]);
        
        // Email gönder
        $template = new EmailTemplate();
        $email = $template->make('user/register', [
            'name' => $user->name,
            'email' => $user->email,
            'activation_link' => base_url('activate/' . $token),
        ]);
        
        $email->to($user->email, $user->name)
              ->subject('Hesabınızı Aktifleştirin')
              ->send();
        
        return redirect()->to('/register/success');
    }
}
```

### Örnek 19: Toplu Duyuru Email'i

```php
<?php

namespace App\Controllers;

use Yakupeyisan\CodeIgniter4\Mailer\Mailer;
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

class AnnouncementController extends BaseController
{
    public function sendAnnouncement()
    {
        $subscribers = $this->subscriberModel->findAll();
        
        $mailer = new Mailer();
        $emails = [];
        
        foreach ($subscribers as $subscriber) {
            $email = new EmailBuilder();
            $email->to($subscriber->email)
                  ->subject('Yeni Duyuru: ' . $this->request->getPost('title'))
                  ->view('emails/announcement', [
                      'name' => $subscriber->name,
                      'title' => $this->request->getPost('title'),
                      'content' => $this->request->getPost('content'),
                      'link' => base_url('announcements/' . $this->request->getPost('id')),
                  ]);
            
            $emails[] = $email;
        }
        
        $results = $mailer->sendBatch($emails);
        
        $success = count(array_filter($results, fn($r) => $r['result']));
        $failed = count($results) - $success;
        
        return $this->response->setJSON([
            'success' => true,
            'sent' => $success,
            'failed' => $failed,
        ]);
    }
}
```

### Örnek 20: Sipariş Onay Email'i

```php
<?php

namespace App\Controllers;

use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

class OrderController extends BaseController
{
    public function confirmOrder($orderId)
    {
        $order = $this->orderModel->find($orderId);
        $user = $this->userModel->find($order->user_id);
        
        // PDF fatura oluştur
        $invoicePdf = $this->generateInvoice($order);
        
        // Email gönder
        $email = new EmailBuilder();
        $email->to($user->email, $user->name)
              ->subject('Sipariş Onayı - #' . $order->order_number)
              ->view('emails/order-confirmation', [
                  'order' => $order,
                  'user' => $user,
                  'items' => $order->items,
              ])
              ->attachData($invoicePdf, 'fatura-' . $order->order_number . '.pdf', 'application/pdf')
              ->send();
        
        return redirect()->to('/orders/' . $orderId);
    }
}
```

### Örnek 21: Hata Raporlama Email'i

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;

class ErrorReportingFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // ...
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($response->getStatusCode() >= 500) {
            // Hata email'i gönder
            $email = new EmailBuilder();
            $email->to('admin@example.com')
                  ->subject('Uygulama Hatası: ' . $request->getUri())
                  ->html("
                      <h2>Hata Raporu</h2>
                      <p><strong>URL:</strong> {$request->getUri()}</p>
                      <p><strong>Method:</strong> {$request->getMethod()}</p>
                      <p><strong>Status Code:</strong> {$response->getStatusCode()}</p>
                      <p><strong>Zaman:</strong> " . date('Y-m-d H:i:s') . "</p>
                      <pre>" . print_r($request->getServer(), true) . "</pre>
                  ")
                  ->priority(1)
                  ->send();
        }
    }
}
```

## İpuçları ve Best Practices

1. **Template Kullanımı**: Her zaman template kullanın, HTML'i kod içine yazmayın.
2. **Queue Kullanımı**: Toplu email gönderimlerinde queue kullanın.
3. **Error Handling**: Her zaman try-catch kullanın.
4. **Validation**: Email adreslerini doğrulayın.
5. **Logging**: Email gönderimlerini loglayın.
6. **Testing**: Test modunda email göndermeyin, loglama yapın.
7. **Rate Limiting**: Toplu gönderimlerde rate limiting kullanın.

