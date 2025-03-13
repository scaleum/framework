<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\Http;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Scaleum\Core\Contracts\ResponderInterface;

/**
 * Response
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ServerResponse extends ClientResponse implements ResponderInterface {
    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        mixed $body = null,
        string $protocol = '1.1'
    ) {
        parent::__construct($statusCode, $headers, $this->createStream($body), $protocol);
    }

    // Преобразует body в Stream и устанавливает правильный Content-Type
    protected function createStream(mixed $body): StreamInterface {
        $stream = new Stream(fopen('php://temp', 'w+'));

        // Если передан объект или массив → конвертируем в JSON
        if (is_array($body) || is_object($body)) {
            $body                            = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $this->headers['Content-Type']   = ['application/json'];
            $this->headers['Content-Length'] = [strlen($body)];
        }
        // Если передан файл → определяем MIME-тип
        elseif ($body && is_file($body)) {
            $mimeType = $this->getMimeType($body);
            if ($mimeType) {
                $this->headers['Content-Type']              = [$mimeType];
                $this->headers['Content-Disposition']       = ['attachment; filename="' . basename($body) . '"'];
                $this->headers['Content-Length']            = [filesize($body)];
                $this->headers['Content-Transfer-Encoding'] = ['binary'];
                $this->headers['Last-Modified']             = [gmdate('D, d M Y H:i:s', filemtime($body)) . ' GMT'];
            }
            return new Stream(fopen($body, 'r+'));
        }
        // Если строка → определяем Content-Type
        elseif (is_string($body)) {
            $this->headers['Content-Type']   = [$this->detectMimeTypeFromContent($body)];
            $this->headers['Content-Length'] = [strlen($body)];
        }
        // Если неизвестный тип → приводим к строке
        elseif (! is_string($body)) {
            $body                            = (string) $body;
            $this->headers['Content-Type']   = [$this->detectMimeTypeFromContent($body)];
            $this->headers['Content-Length'] = [strlen($body)];
        }

        $stream->write($body);
        $stream->rewind();

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

    // Отправка HTTP-ответа клиенту
    public function send(): void {
        header(sprintf('HTTP/%s %d %s', $this->protocol, $this->statusCode, $this->getReasonPhrase()), true, $this->statusCode);

        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        fpassthru($this->body->detach());
    }
}
/** End of ServerResponse **/