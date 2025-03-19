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
use Scaleum\Stdlib\Helpers\HttpHelper;

/**
 * ClientResponse
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ClientResponse extends Message implements ResponseInterface {
    private mixed $parsedBody = null;
    protected int $statusCode;
    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        ?StreamInterface $body = null,
        string $protocol = '1.1'
    ) {
        parent::__construct($headers, $body, $protocol);

        $this->parsedBody = self::parseBody($this->body, $this->getHeaderLine('Content-Type'));
        $this->statusCode = $statusCode;
    }

    public static function parseBody(StreamInterface $body, string $contentType): mixed {
        // Сохраняем текущую позицию в потоке
        $originalPosition = null;
        if ($body->isSeekable()) {
            $originalPosition = $body->tell();
        }

        // Получаем содержимое потока
        $data = $body->getContents();

        // Восстанавливаем позицию, если возможно
        if ($originalPosition !== null) {
            $body->seek($originalPosition);
        }

        // Разбираем данные в зависимости от Content-Type
        switch (true) {
        case str_contains($contentType, 'application/json'):
            return json_decode($data, true) ?? [];

        case str_contains($contentType, 'application/x-www-form-urlencoded'):
            parse_str($data, $parsedData);
            return $parsedData;

        case str_contains($contentType, 'text/plain'):
            return $data; // Просто строка

        case str_contains($contentType, 'multipart/form-data'):
            return self::parseMultipartFormData($data, $contentType);

        default:
            return $data; // Если тип неизвестен, возвращаем как есть
        }
    }

    private static function parseMultipartFormData(string $data, string $contentType): array {
        $boundary = self::extractBoundary($contentType);
        if (! $boundary) {
            return ['error' => 'Boundary not found'];
        }

        $parts      = explode("--$boundary", $data);
        $parsedData = [];

        foreach ($parts as $part) {
            if (empty(trim($part)) || str_contains($part, '--')) {
                continue;
            }

            preg_match('/Content-Disposition: form-data; name="(.+?)"(; filename="(.+?)")?/i', $part, $matches);
            if (! isset($matches[1])) {
                continue;
            }

            $name     = $matches[1];
            $filename = $matches[3] ?? null;
            $content  = preg_replace('/^.*\r\n\r\n/s', '', $part); // Убираем заголовки

            $parsedData[$name] = $filename ? ['filename' => $filename, 'content' => trim($content)] : trim($content);
        }

        return $parsedData;
    }
    private static function extractBoundary(string $contentType): ?string {
        if (preg_match('/boundary=(.+)$/', $contentType, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): static
    {
        $clone             = clone $this;
        $clone->statusCode = $code;
        return $clone;
    }

    public function getReasonPhrase(): string {
        return HttpHelper::getStatusMessage($this->statusCode);
    }

    public function getParsedBody(): mixed {
        return $this->parsedBody;
    }
}
/** End of ClientResponse **/