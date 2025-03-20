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

use Psr\Http\Message\StreamInterface;
/**
 * StreamTrait
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
trait StreamTrait {
    protected function prepareHeadersAndStream(array $_headers, mixed $_body): array {
        // If the body is already a StreamInterface instance, return it as is
        if ($_body instanceof StreamInterface) {
            return [$_headers, $_body];
        }

        $stream  = new Stream(fopen('php://temp', 'w+'));
        $headers = new HeadersManager($_headers);

        // If an object or array is passed → convert to JSON
        if (is_array($_body) || is_object($_body)) {
            $_body = json_encode($_body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $headers->setHeader('Content-Type', 'application/json');
            $headers->setHeader('Content-Length', (string) strlen($_body));
        }
        // If a file is passed → determine the MIME type
        elseif ($_body && is_file($_body)) {
            if ($mimeType = $this->getMimeType($_body)) {
                $headers->setHeader('Content-Type', $mimeType);
                $headers->setHeader('Content-Disposition', 'attachment; filename="' . basename($_body) . '"');
                $headers->setHeader('Content-Length', (string) filesize($_body));
                $headers->setHeader('Content-Transfer-Encoding', 'binary');
                $headers->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($_body)) . ' GMT');
            }
            return [new Stream(fopen($_body, 'r+')), $headers->getAll()];
        }
        // If a string is passed → determine the Content-Type
        elseif (is_string($_body)) {
            $headers->setHeader('Content-Type', $this->detectMimeTypeFromContent($_body));
            $headers->setHeader('Content-Length', (string) mb_strlen($_body));
        }
        // If an unknown type is passed → convert to string
        elseif (! is_string($_body)) {
            $_body = (string) $_body;
            $headers->setHeader('Content-Type', $this->detectMimeTypeFromContent($_body));
            $headers->setHeader('Content-Length', (string) mb_strlen($_body));
        }

        $stream->write($_body);
        $stream->rewind();

        // Return an array of headers and a stream
        return [$headers->getAll(), $stream];
    }

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