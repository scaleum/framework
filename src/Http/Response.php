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
use Scaleum\Stdlib\Helpers\HttpHelper;

/**
 * Response
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Response implements ResponderInterface, ResponseInterface {
    protected int $statusCode;
    protected array $headers;
    protected StreamInterface $body;
    protected string $reasonPhrase;
    protected string $protocolVersion;

    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        mixed $body = null,
        string $protocolVersion = '1.1',
        string $reasonPhrase = ''
    ) {
        $this->statusCode      = $statusCode;
        $this->headers         = $headers;
        $this->protocolVersion = $protocolVersion;
        $this->body            = $this->createStream($body);
        $this->reasonPhrase    = $reasonPhrase ?: $this->getReasonPhrase();
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
        header(sprintf('HTTP/%s %d %s', $this->protocolVersion, $this->statusCode, $this->getReasonPhrase()), true, $this->statusCode);

        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        fpassthru($this->body->detach());
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): self {
        $clone               = clone $this;
        $clone->statusCode   = $code;
        $clone->reasonPhrase = $reasonPhrase ?: $this->getReasonPhrase();
        return $clone;
    }

    public function getProtocolVersion(): string {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): self {
        $clone                  = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function hasHeader($name): bool {
        return isset($this->headers[$name]);
    }

    public function getHeader($name): array {
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name): string {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): self {
        $clone                 = clone $this;
        $clone->headers[$name] = is_array($value) ? $value : [$value];
        return $clone;
    }

    public function withAddedHeader($name, $value): self {
        $clone                 = clone $this;
        $clone->headers[$name] = array_merge($this->headers[$name] ?? [], is_array($value) ? $value : [$value]);
        return $clone;
    }

    public function withoutHeader($name): self {
        $clone = clone $this;
        unset($clone->headers[$name]);
        return $clone;
    }

    public function getBody(): StreamInterface {
        return $this->body;
    }

    public function withBody(StreamInterface $body): self {
        $clone       = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function getReasonPhrase(): string {
        return HttpHelper::getStatusMessage($this->statusCode);
    }
}
/** End of Response **/