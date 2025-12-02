<?php

namespace Yakupeyisan\CodeIgniter4\Mailer\Templates;

use Yakupeyisan\CodeIgniter4\Mailer\Config\Mailer as MailerConfig;
use Yakupeyisan\CodeIgniter4\Mailer\EmailBuilder;
use Yakupeyisan\CodeIgniter4\Mailer\Exceptions\EmailException;

class EmailTemplate
{
    protected MailerConfig $config;
    protected string $templatePath;

    public function __construct(?MailerConfig $config = null)
    {
        $this->config = $config ?? config('Mailer');
        $this->templatePath = rtrim($this->config->templatePath, '/') . '/';
    }

    /**
     * Template'den email oluştur
     */
    public function make(string $template, array $data = []): EmailBuilder
    {
        $builder = new EmailBuilder($this->config);
        
        // Template dosyasını yükle
        $htmlTemplate = $this->loadTemplate($template . '.html');
        $textTemplate = $this->loadTemplate($template . '.txt');
        
        // Data ile doldur
        if ($htmlTemplate) {
            $html = $this->render($htmlTemplate, $data);
            $builder->html($html);
        }
        
        if ($textTemplate) {
            $text = $this->render($textTemplate, $data);
            $builder->text($text);
        }
        
        if (!$htmlTemplate && !$textTemplate) {
            throw new EmailException("Template bulunamadı: {$template}");
        }
        
        return $builder;
    }

    /**
     * Template dosyasını yükle
     */
    protected function loadTemplate(string $filename): ?string
    {
        $filePath = $this->templatePath . $filename;
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        return file_get_contents($filePath);
    }

    /**
     * Template'i render et
     */
    protected function render(string $template, array $data): string
    {
        // Basit placeholder replacement
        // {{variable}} veya {variable} formatında
        $content = $template;
        
        foreach ($data as $key => $value) {
            $placeholders = [
                '{{' . $key . '}}',
                '{' . $key . '}',
                '{{ $' . $key . ' }}',
                '{ $' . $key . ' }',
            ];
            
            foreach ($placeholders as $placeholder) {
                $content = str_replace($placeholder, $value, $content);
            }
        }
        
        // CodeIgniter View renderer kullanılabilir
        if (class_exists('\Config\Services')) {
            try {
                $view = \Config\Services::renderer();
                $content = $view->setData($data)->renderString($template);
            } catch (\Exception $e) {
                // View renderer başarısız olursa basit replacement kullan
            }
        }
        
        return $content;
    }

    /**
     * Template path'i ayarla
     */
    public function setTemplatePath(string $path): self
    {
        $this->templatePath = rtrim($path, '/') . '/';
        return $this;
    }
}

