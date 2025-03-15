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
use RuntimeException;

/**
 * Stream
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Stream implements StreamInterface {
    private $stream;
    private bool $seekable;
    private bool $readable;
    private bool $writable;
    private array $meta;

    public function __construct($resource) {
        if (! is_resource($resource)) {
            throw new RuntimeException('Stream must be a valid resource.');
        }

        $this->stream   = $resource;
        $this->meta     = stream_get_meta_data($this->stream);
        $this->seekable = $this->meta['seekable'];
        $this->readable = strpbrk($this->meta['mode'], 'r+') !== false;
        $this->writable = strpbrk($this->meta['mode'], 'waxc+') !== false;
    }

    public function __toString(): string {
        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }
            return $this->getContents();
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function close(): void {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    public function detach() {
        $stream         = $this->stream;
        $this->stream   = null;
        $this->seekable = false;
        $this->readable = false;
        $this->writable = false;
        return $stream;
    }

    public function getSize(): ?int {
        if (! is_resource($this->stream)) {
            return null;
        }

        $stats = fstat($this->stream);
        return $stats['size'] ?? null;
    }

    public function tell(): int {
        if (! is_resource($this->stream)) {
            throw new RuntimeException('Stream is not available.');
        }

        $position = ftell($this->stream);
        if ($position === false) {
            throw new RuntimeException('Unable to determine stream position.');
        }

        return $position;
    }

    public function eof(): bool {
        return ! is_resource($this->stream) || feof($this->stream);
    }

    public function isSeekable(): bool {
        return $this->seekable;
    }

    public function seek($offset, $whence = SEEK_SET): void {
        if (! $this->isSeekable() || ! is_resource($this->stream)) {
            throw new RuntimeException('Stream is not seekable.');
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Failed to seek stream.');
        }
    }

    public function rewind(): void {
        $this->seek(0);
    }

    public function isWritable(): bool {
        return $this->writable;
    }

    public function write(string $string): int {
        if (! $this->isWritable() || ! is_resource($this->stream)) {
            throw new RuntimeException('Stream is not writable.');
        }

        $bytesWritten = fwrite($this->stream, $string);
        if ($bytesWritten === false) {
            throw new RuntimeException('Failed to write to stream.');
        }

        return $bytesWritten;
    }

    public function isReadable(): bool {
        return $this->readable;
    }

    public function read($length): string {
        if (! $this->isReadable() || ! is_resource($this->stream)) {
            throw new RuntimeException('Stream is not readable.');
        }

        $data = fread($this->stream, $length);
        if ($data === false) {
            throw new RuntimeException('Failed to read from stream.');
        }

        return $data;
    }

    public function getContents(): string {
        if (! $this->isReadable() || ! is_resource($this->stream)) {
            throw new RuntimeException('Stream is not available.');
        }

        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new RuntimeException('Failed to read stream contents.');
        }

        return $contents;
    }

    public function getMetadata($key = null): mixed {
        if (! is_resource($this->stream)) {
            return $key === null ? [] : null;
        }
        return $key === null ? $this->meta : ($this->meta[$key] ?? null);
    }
}
/** End of Stream **/