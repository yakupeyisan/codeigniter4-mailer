# Email Queue Migration

Email queue özelliğini kullanmak için veritabanı tablosu oluşturmanız gerekir.

## Migration Dosyası

`app/Database/Migrations/` klasörüne aşağıdaki migration dosyasını oluşturun:

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
                'null' => false,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'sent', 'failed'],
                'default' => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('email_queue');
    }

    public function down()
    {
        $this->forge->dropTable('email_queue');
    }
}
```

## Migration Çalıştırma

```bash
php spark migrate
```

## Tablo Yapısı

| Sütun | Tip | Açıklama |
|-------|-----|----------|
| id | INT | Primary key |
| email_data | TEXT | JSON formatında email verisi |
| status | ENUM | pending, sent, failed |
| created_at | DATETIME | Oluşturulma tarihi |
| sent_at | DATETIME | Gönderilme tarihi |
| attempts | INT | Deneme sayısı |
| error_message | TEXT | Hata mesajı |

## Kullanım

Migration çalıştırıldıktan sonra queue özelliğini kullanabilirsiniz:

```php
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Queue\EmailQueue;

$email = new EmailBuilder();
$email->to('user@example.com')
      ->subject('Test')
      ->html('<p>Test</p>');

$queue = new EmailQueue();
$queue->push($email);
```

