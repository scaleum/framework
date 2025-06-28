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

/**
 * HeadersManager
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class HeadersManager {
    private array $headers;

    public function __construct(array $headers = []) {
        $this->setHeaders($headers);
    }

    /**
     * Проверяет, есть ли заголовок
     */
    public function hasHeader(string $name): bool {
        return isset($this->headers[$name]);
    }

    /**
     * Получает значение заголовка, если есть — первый элемент, иначе возвращает значение по умолчанию
     */
    public function getHeader(string $name, mixed $default = null): mixed {
        if (isset($this->headers[$name]) && count($this->headers[$name]) > 0) {
            return $this->headers[$name][0];
        }
        return $default;
    }

    /**
     * Получает первое значение заголовка или null
     */
    public function getHeaderLine(string $name): ?string {
        return $this->headers[$name][0] ?? null;
    }

    /**
     * Устанавливает заголовок (перезаписывает существующий)
     */
    public function setHeader(string $name, string $value, bool $override = true): void {
        if(!$override && $this->hasHeader($name)) {
            return; // Если не нужно перезаписывать, выходим
        }

        $value                = array_map('trim', explode(',', $value));
        $this->headers[$name] = $value;
    }

    public function setHeaders(array $array, bool $reset = false): void {
        if ($reset) {
            $this->clear();
        }

        foreach ($array as $name => $values) {
            if (is_array($values)) {
                foreach ($values as $value) {
                    $this->addHeader($name, $value);
                }
            } else {
                $this->setHeader($name, $values);
            }
        }
    }

    /**
     * Добавляет новое значение к заголовку (не перезаписывает)
     */
    public function addHeader(string $name, string $value): void {
        $values = array_map('trim', explode(',', $value));
        if (! $this->hasHeader($name)) {
            $this->headers[$name] = $values;
        } else {
            $this->headers[$name] = array_merge($this->headers[$name], $values);
        }
    }

    /**
     * Удаляет заголовок
     */
    public function removeHeader(string $name): void {
        unset($this->headers[$name]);
    }

    /**
     * Возвращает все заголовки
     */
    public function getAll(): array {
        return $this->headers;
    }

    /**
     * Возвращает массив заголовков в виде строк "Header-Name: value1, value2"
     */
    public function getAsStrings(): array {
        $result = [];
        foreach ($this->headers as $name => $values) {
            $result[] = $name . ': ' . implode(', ', $values);
        }
        return $result;
    }

    /**
     * Возвращает массив заголовков в виде ассоциативного массива "Header-Name" => "value1, value2"
     */
    public function getAsFlattened(): array {
        $result = [];
        foreach ($this->headers as $name => $values) {
            $result[$name] = implode(', ', $values);
        }
        return $result;
    }

    public function getCount(): int {
        return count($this->headers);
    }

    public function clear(): void {
        $this->headers = [];
    }
}
/** End of HeadersManager **/