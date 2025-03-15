<?php
declare(strict_types=1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Http;


use Psr\Http\Message\StreamInterface;
/**
 * StreamTrait
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
trait StreamTrait
{
    protected function createStream(mixed $body): StreamInterface {
        // If the body is already a StreamInterface instance, return it as is
        if($body instanceof StreamInterface) {
            return $body;
        }

        $stream  = new Stream(fopen('php://temp', 'w+'));
        $headers = new HeadersManager($this->headers);

        // Если передан объект или массив → конвертируем в JSON
        if (is_array($body) || is_object($body)) {
            $body = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $headers->setHeader('Content-Type', 'application/json');
            $headers->setHeader('Content-Length', (string) strlen($body));
        }
        // Если передан файл → определяем MIME-тип
        elseif ($body && is_file($body)) {
            if ($mimeType = $this->getMimeType($body)) {
                $headers->setHeader('Content-Type', $mimeType);
                $headers->setHeader('Content-Disposition', 'attachment; filename="' . basename($body) . '"');
                $headers->setHeader('Content-Length', (string) filesize($body));
                $headers->setHeader('Content-Transfer-Encoding', 'binary');
                $headers->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($body)) . ' GMT');
            }
            return new Stream(fopen($body, 'r+'));
        }
        // Если строка → определяем Content-Type
        elseif (is_string($body)) {
            $headers->setHeader('Content-Type', $this->detectMimeTypeFromContent($body));
            $headers->setHeader('Content-Length', (string) mb_strlen($body));
        }
        // Если неизвестный тип → приводим к строке
        elseif (! is_string($body)) {
            $body = (string) $body;
            $headers->setHeader('Content-Type', $this->detectMimeTypeFromContent($body));
            $headers->setHeader('Content-Length', (string) mb_strlen($body));
        }

        $stream->write($body);
        $stream->rewind();

        // Устанавливаем заголовки
        $this->headers = $headers->getAll();
        return $stream;
    }

    // Определяет MIME-тип по содержимому строки
    private function detectMimeTypeFromContent(string $content): string {
        if (preg_match('/^\s*[{[]/', $content)) {
            return 'application/json';
        }

        if (preg_match('/^\s*</', $content)) {
            return 'text/html';
        }

        if (stripos($content, '<?xml') !== false) {
            return 'application/xml';
        }

        if (stripos($content, '<?php') !== false) {
            return 'application/x-httpd-php';
        }

        if (preg_match('/^(SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER|TRUNCATE|REPLACE|WITH)\s/i', $content)) {
            return 'text/sql';
        }

        return 'text/plain';
    }

    // Определение MIME-типа по расширению файла
    private function getMimeType(string $filePath): ?string {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'txt'  => 'text/plain',
            'html' => 'text/html',
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'xml'  => 'application/xml',
            'csv'  => 'text/csv',
            'sql'  => 'text/sql',
            'pdf'  => 'application/pdf',
            'zip'  => 'application/zip',
            'tar'  => 'application/x-tar',
            'gz'   => 'application/gzip',
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'mp3'  => 'audio/mpeg',
            'wav'  => 'audio/wav',
            'mp4'  => 'video/mp4',
        ];

        return $mimeTypes[$extension] ?? null;
    }
}
/** End of StreamTrait **/