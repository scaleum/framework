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
use Scaleum\Stdlib\Helpers\ArrayHelper;
use Scaleum\Stdlib\Helpers\HttpHelper;
use Scaleum\Stdlib\Helpers\XmlHelper;

/**
 * MessagePayloadTrait
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
trait MessagePayloadTrait {
    protected function getMessagePayload(array $headers, mixed $body): MessagePayload {
        // If the body is already a StreamInterface instance, return it as is
        if ($body instanceof StreamInterface) {
            return new MessagePayload($headers, $body);
        }

        $stream         = new Stream(fopen('php://temp', 'w+'));
        $headersManager = new HeadersManager($headers);

        // If an object or array is passed → convert to JSON or XML
        if (is_array($body) || is_object($body)) {
            $format = HttpHelper::getAcceptFormat();
            switch ($format) {
            case HttpHelper::FORMAT_XML:
                $body = ArrayHelper::castToXml($body);
                $headersManager->setHeader('Content-Type', 'application/xml; charset=utf-8');
                $headersManager->setHeader('Content-Length', (string) strlen($body));
                break;
            case HttpHelper::FORMAT_SERIALIZED:
                $body = ArrayHelper::castToSerialize($body);
                $headersManager->setHeader('Content-Type', 'application/vnd.php.serialized; charset=utf-8');
                break;
            case HttpHelper::FORMAT_JSON:
            case HttpHelper::FORMAT_JSONP:
            default:
                $body = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $headersManager->setHeader('Content-Type', 'application/json; charset=utf-8');
                $headersManager->setHeader('Content-Length', (string) strlen($body));
            }
        }
        // If a file is passed → determine the MIME type
        elseif ($body && is_file($body)) {
            if ($mimeType = $this->getMimeType($body)) {
                $headersManager->setHeader('Content-Type', $mimeType);
                $headersManager->setHeader('Content-Disposition', 'attachment; filename="' . basename($body) . '"');
                $headersManager->setHeader('Content-Length', (string) filesize($body));
                $headersManager->setHeader('Content-Transfer-Encoding', 'binary');
                $headersManager->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($body)) . ' GMT');
            }
            return new MessagePayload($headersManager->getAll(), new Stream(fopen($body, 'r+')));
        }
        // If a string is passed → determine the Content-Type
        elseif (is_string($body)) {
            $headersManager->setHeader('Content-Type', $this->detectMimeTypeFromContent($body));
            $headersManager->setHeader('Content-Length', (string) strlen($body));
        }
        // If an unknown type is passed → convert to string
        elseif (! is_string($body)) {
            $body = (string) $body;
            $headersManager->setHeader('Content-Type', $this->detectMimeTypeFromContent($body));
            $headersManager->setHeader('Content-Length', (string) strlen($body));
        }

        $stream->write($body);
        $stream->rewind();

        return new MessagePayload($headersManager->getAll(), $stream);
    }

    private function detectMimeTypeFromContent(string $content): string {
        if (preg_match('/^\s*[{[]/', $content)) {
            return 'application/json';
        }

        if (stripos($content, '<?xml') !== false || XmlHelper::isXml($content)) {
            return 'application/xml';
        }

        if (stripos($content, '<?php') !== false) {
            return 'application/x-httpd-php';
        }

        if (preg_match('/^(SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER|TRUNCATE|REPLACE|WITH)\s/i', $content)) {
            return 'text/sql';
        }

        if (preg_match('/^\s*</', $content)) {
            return 'text/html';
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
/** End of MessagePayloadTrait **/