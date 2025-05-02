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

namespace Scaleum\Storages\PDO\Builders;

use Scaleum\Stdlib\Exceptions\EDatabaseError;
use Scaleum\Storages\PDO\Database;
use Scaleum\Storages\PDO\DatabaseProvider;
use Scaleum\Storages\PDO\Helpers\DatabaseHelper;

/**
 * BuilderAbstract
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
abstract class BuilderAbstract extends DatabaseProvider {
    protected static array $adapters       = [];
    protected string $identifierQuoteLeft  = "`";
    protected string $identifierQuoteRight = "`";
    protected array $reservedIdentifiers   = ['*'];
    protected bool $prepare                = false;
    protected bool $optimize               = true;

    public static function create(string $driverType, array $args = []): static {
        if (! isset(static::$adapters[$driverType])) {
            throw new EDatabaseError(sprintf('Adapter for driver `%s` not found', $driverType));
        }

        if (class_exists($className = static::$adapters[$driverType])) {
            return new $className(...$args);
        } else {
            throw new EDatabaseError(sprintf('Adapter class `%s` not found', $className));
        }
    }

    public function __construct(?Database $database) {
        parent::__construct($database);
    }

    /**
     * Get the value of prepared
     */
    public function getPrepare(): bool {
        return $this->prepare;
    }

    /**
     * Set the value of prepared
     *
     * @return  self
     */
    public function setPrepare(bool $prepare): self {
        $this->prepare = $prepare;
        return $this;
    }

    protected function flush(): self {
        $this->prepare  = false;
        $this->optimize = true;
        return $this;
    }

    /**
     * Get the value of optimize
     */
    public function getOptimize(): bool {
        return $this->optimize;
    }

    /**
     * Set the value of optimize
     *
     * @return  self
     */
    public function setOptimize(bool $optimize): self {
        $this->optimize = $optimize;
        return $this;
    }

    public function getOptimizedQuery(string $sql): string {
        $sql = trim($sql);
        $sql = preg_replace('/\s+/', ' ', $sql);
        return $sql;
    }

    public function getPrettyQuery(string $sql): string {
        $sql       = $this->getOptimizedQuery($sql);
        $prettySql = preg_replace(
            [
                '/\b(SELECT|FROM|UNION|UNION ALL|WHERE|GROUP BY|ORDER BY|LIMIT|OFFSET|JOIN|HAVING|VALUES|SET|CREATE|ALTER)\b/i',
                '/\b(AND|OR|CASE|END)\b/i',
                '/\b(WHEN|THEN|ELSE)\b/i',
            ],
            [
                PHP_EOL . '$1',     // Add newline before FROM|WHERE|GROUP BY|ORDER BY|LIMIT|OFFSET|JOIN|ON|HAVING|VALUES|SET
                PHP_EOL . '  $1',   // Add newline before AND|OR|CASE|END
                PHP_EOL . '    $1', // Add newline before WHEN|THEN|ELSE
            ],
            $sql
        );

        return trim($prettySql);
    }

    protected function realize(string $sql, array $params = [], string $method = 'execute', array $args = []): mixed {
        if (! method_exists($db = $this->getDatabase(), $method)) {
            throw new \Exception(sprintf('Method `%s` not found in `%s`', $method, get_class($db)));
        }

        $sql    = ($this->optimize == FALSE) ? $this->getPrettyQuery($sql) : $this->getOptimizedQuery($sql);
        $result = ($this->prepare == TRUE) ? $sql : $db->setQuery($sql, $params)->$method($args);

        $this->flush();

        return $result;
    }

    protected function makeSQL(): string {
        return '';
    }

    public function __toString() {
        return $this->makeSQL();
    }

    protected function quote(mixed $value): mixed {
        return DatabaseHelper::quote($this->getDatabase()->getPDO(), $value);
    }

    protected function quoteIdentifier(string $identifier): string {

        $quoteL = $this->identifierQuoteLeft;
        $quoteR = $this->identifierQuoteRight;

        if (empty($quoteL) || empty($quoteR)) {
            return $identifier;
        }

        $identifier = explode(".", $identifier);
        $identifier = array_map(
            function ($part) use ($quoteL, $quoteR) {
                if (in_array($part, $this->reservedIdentifiers)) {
                    return $part;
                } else {
                    $part = str_replace([$quoteL, $quoteR], '', $part);
                    return sprintf("%s%s%s", $quoteL, $part, $quoteR);
                }
            },
            $identifier
        );

        return implode(".", $identifier);
    }

    protected function protectIdentifiers(array | string $item, bool $protect = true) {
        if (is_array($item)) {
            $result = [];
            foreach ($item as $key => $value) {
                // [x] We don't need to protect keys, they are not used in SQL queries
                // $result[$this->protectIdentifiers((string)$key)] = $this->protectIdentifiers($value);

                $result[$key] = $this->protectIdentifiers($value);
            }

            return $result;
        }

        // Convert tabs or multiple spaces into single spaces
        $item = preg_replace('/[\t\n ]+/', ' ', (string) $item);

        // If the item has an alias declaration we remove it and set it aside.
        // Basically we remove everything to the right of the first space
        if ($item != null && strpos($item, ' ') !== false) {
            $alias = strstr($item, ' ');
            $item  = substr($item, 0, -strlen($alias));
        } else {
            $alias = '';
        }

        // This is basically a bug fix for queries that use MAX, MIN, etc.
        // If a parenthesis is found we know that we do not need to
        // escape the data or add a prefix.  There's probably a more graceful
        // way to deal with this, but I'm not thinking of it -- Rick
        if (strpos($item, '(') !== false) {
            return "$item$alias";
        }

        // Break the string apart if it contains periods, then insert the table prefix
        // in the correct location, assuming the period doesn't indicate that we're dealing
        // with an alias. While we're at it, we will escape the components
        if (strpos($item, '.') !== false) {
            if ($protect === true) {
                $item = $this->quoteIdentifier($item);
            }

            return "$item$alias";
        }

        if ($protect === true and ! in_array($item, $this->reservedIdentifiers)) {
            $item = $this->quoteIdentifier($item);
        }

        return "$item$alias";
    }

    public function getUniqueName(array $columns, string $prefix = 'key'): string {
        $baseName = $prefix . '_' . implode('_', $columns);
        $suffix   = bin2hex(random_bytes(4)); // 8 hex-символов, практически нулевая коллизия

        return "{$baseName}_{$suffix}";
    }
}
/** End of BuilderAbstract **/