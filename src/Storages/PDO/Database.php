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

namespace Scaleum\Storages\PDO;

use PDO;
use Scaleum\Cache\Cache;
use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Storages\PDO\Builders\Contracts\QueryBuilderInterface;
use Scaleum\Storages\PDO\Builders\Contracts\SchemaBuilderInterface;
use Scaleum\Storages\PDO\Builders\QueryBuilder;
use Scaleum\Storages\PDO\Builders\SchemaBuilder;
use Scaleum\Storages\PDO\Exceptions\ESQLError;
use Scaleum\Storages\PDO\Helpers\DatabaseHelper;

/**
 * Database
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class Database extends Hydrator {
    protected string $dsn;
    protected string $user;
    protected string $password;
    protected bool $multipleCommands = false;
    private bool $connected          = false;
    protected array $options         = [];
    private ?PDO $pdo                = null;
    protected ?Cache $cache          = null;
    private int $queryCounter        = 0;
    private array $queryParams       = [];
    private int $queryRowsAffected   = 0;
    private string $queryStr         = '';

    public function begin(): bool {
        return $this->getPDO()->beginTransaction();
    }

    public function commit(): bool {
        return $this->getPDO()->commit();
    }

    public function rollback(): bool {
        return $this->getPDO()->rollBack();
    }

    /**
     * Get the PDO instance.
     *
     * This method returns the PDO instance used for database interactions.
     *
     * @return PDO The PDO instance.
     */
    public function getPDO(): PDO {
        if (! $this->connected || $this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }

    /**
     * Sets the PDO instance.
     *
     * @param PDO $instance The PDO instance to set.
     * @return static
     */
    public function setPDO(PDO $instance) {
        $this->pdo       = $instance;
        $this->connected = false;
        return $this;
    }

    /**
     * Get the name of the PDO driver.
     *
     * @return mixed The name of the PDO driver.
     */
    public function getPDODriverName(): mixed {
        return $this->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Retrieves the version of the PDO extension.
     *
     * @return mixed The version of the PDO extension.
     */
    public function getPDOVersion(): mixed {
        return $this->getPDO()->getAttribute(PDO::ATTR_CLIENT_VERSION);
    }

    /**
     * Retrieves the signature of the database connection.
     *
     * @return string The signature of the database connection.
     */
    public function getSignature() {
        return md5($this->dsn . serialize($this->options));
    }

    /**
     * Retrieves the last executed SQL query.
     *
     * @return array The last executed SQL query info.
     */
    public function getLastQuery() {
        return [
            'query_str'     => $this->queryStr,
            'params'        => $this->queryParams,
            'rows_affected' => $this->queryRowsAffected,
        ];
    }

    public function getLastQueryRowsAffected(): int {
        return $this->queryRowsAffected;
    }

    /**
     * Retrieves the ID of the last inserted row.
     *
     * @param string|null $sequence The name of the sequence object from which the ID should be returned.
     *                              If not specified, the ID of the last inserted row will be returned.
     * @return mixed The ID of the last inserted row.
     */
    public function getLastInsertID(?string $sequence = null):mixed {
        return $this->getPDO()->lastInsertId($sequence);
    }

    /**
     * Retrieves the cache instance.
     *
     * @return Cache The cache instance.
     */
    public function getCache(): Cache {
        if ($this->cache === null) {
            $this->cache = new Cache();
        }
        return $this->cache;
    }

    /**
     * Generates and returns a cache key as a string.
     *
     * @return string The generated cache key.
     */
    public function getCacheKey(): string {
        $result = '';
        $args   = func_get_args();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $result .= serialize($arg);
            } else {
                $result .= $arg;
            }
        }

        return md5($result);
    }

    /**
     * Sets the cache instance.
     *
     * @param mixed $instance The cache instance to be set.
     * @return static
     */
    public function setCache(mixed $instance) {
        if (is_array($instance)) {
            $instance = static::createInstance($instance);
        }

        if (! $instance instanceof Cache) {
            throw new \InvalidArgumentException(sprintf('Cache must be an instance of `%s`', Cache::class));
        }

        $this->cache = $instance;
        return $this;
    }

    /**
     * Determines if the given SQL query is cacheable.
     *
     * @param string $sql The SQL query to check.
     * @return bool True if the query is cacheable, false otherwise.
     */
    public function isCacheable(string $sql): bool {
        return preg_match('/^\s*(SELECT|SHOW|DESCRIBE)\b/i', $sql) > 0;
    }

    /**
     * Prepares and returns an SQL query string with the provided parameters.
     *
     * @param string|null $sql The SQL query string to be prepared. If null, a default query will be used.
     * @param array $params An associative array of parameters to bind to the SQL query.
     * @return string The prepared SQL query string.
     */
    public function getQuery(?string $sql = null, array $params = []): string {
        if (empty($sql)) {
            $sql = $this->queryStr;
        }

        if (empty($params)) {
            $params = $this->queryParams;
        }

        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $pattern = '/:' . preg_quote(trim(is_integer($key) ? $key + 1 : $key, " \n\r\t\v\x00\:"), '/') . '\b/';
                $sql     = preg_replace($pattern, DatabaseHelper::quote($this->getPDO(), $value), $sql);
            }
        }

        return $sql;
    }

    /**
     * Sets the SQL query and its parameters.
     *
     * @param string $sql The SQL query to be executed.
     * @param array $params Optional. An associative array of parameters to bind to the SQL query.
     */
    public function setQuery(string $sql, array $params = []) {
        foreach ($params as &$value) {
            if (is_array($value)) {
                $array  = array_values($value);
                $pieces = [];
                array_walk($array, function ($element) use (&$pieces) {
                    $pieces[] = DatabaseHelper::quote($this->getPDO(), $element);
                });
                $value = join(',', $pieces);
            } else {
                $value = DatabaseHelper::quote($this->getPDO(), $value);
            }
        }

        $this->queryStr    = $sql;
        $this->queryParams = $params;
// var_export($this->queryStr);
        return $this;
    }

    public function splitQuery(string $sql): array {
        $statements      = [];
        $buffer          = '';
        $inString        = false;
        $stringDelimiter = null;

        for ($i = 0, $len = strlen($sql); $i < $len; $i++) {
            $char = $sql[$i];

            // Проверяем, не открыта ли строка
            if ($inString) {
                if ($char === $stringDelimiter) {
                    // Проверяем, не экранирована ли кавычка
                    if ($i + 1 < $len && $sql[$i + 1] === $stringDelimiter) {
                        $buffer .= $char; // Двойная кавычка внутри строки
                        $i++;
                    } else {
                        $inString = false; // Закрываем строку
                    }
                }
            } elseif ($char === "'" || $char === '"') {
                $inString        = true;
                $stringDelimiter = $char;
            } elseif ($char === ';') {
                // Разделяем по `;`, если не внутри строки
                $statements[] = trim($buffer);
                $buffer       = '';
                continue;
            }

            $buffer .= $char;
        }

        if (! empty(trim($buffer))) {
            $statements[] = trim($buffer);
        }

        return $statements;
    }

    public function execute() {
        return $this->executeInternal();
    }

    public function fetch(array $args = []) {
        return $this->executeInternal(null, [], 'fetch', $args);
    }

    public function fetchAll(array $args = []) {
        return $this->executeInternal(null, [], 'fetchAll', $args);
    }

    public function fetchColumn(array $args = []) {
        return $this->executeInternal(null, [], 'fetchColumn', $args);
    }

    public function getQueryBuilder(): QueryBuilderInterface {
        return QueryBuilder::create($this->getPDODriverName(), [$this]);
    }

    public function getSchemaBuilder(): SchemaBuilderInterface {
        return SchemaBuilder::create($this->getPDODriverName(), [$this]);
    }

    public function getMultipleCommands() {
        return $this->multipleCommands;
    }

    public function setMultipleCommands(bool $bool) {
        $this->multipleCommands = $bool;
        return $this;
    }

    protected function executeInternal(?string $query = null, array $params = [], ?string $method = null, array $fetchArgs = []) {
        $sql = $this->getQuery($query, $params);
        if ($this->getCache()->isEnabled() && $this->isCacheable($sql)) {
            $cacheKey = $this->getCacheKey($sql, $method, $this->getSignature());
            if ($cached = $this->getCache()->get($cacheKey)) {
                return $cached;
            }
        }

        if ($this->getMultipleCommands()) {
            $statements = $this->splitQuery($sql);
            if (count($statements) > 1) {
                $result = [];
                foreach ($statements as $statement) {
                    if (empty($statement)) {
                        continue;
                    }

                    $result[] = $this->executeInternal($statement, [], $method, $fetchArgs);
                }
                unset($statements);
                return $result;
            }
            unset($statements);
        }

        $result = false;
        if (!empty($sql) && $statement = $this->getPDO()->prepare($sql)) {
            try {
                $this->beforeExecute();
                $statement->execute();
                $result                  = empty($method) ? $statement->rowCount() : call_user_func_array([$statement, $method], $fetchArgs);
                $this->queryRowsAffected = is_array($result) ? count($result) : $statement->rowCount();
                $statement->closeCursor();
                $this->afterExecute();
            } catch (\PDOException $err) {
                $errorInfo = $statement->errorInfo();
                $error     = new ESQLError($errorInfo[2], $errorInfo[1] ?? 0);
                if ($errorInfo[0] != $errorInfo[1]) {
                    $error->setSQLState($errorInfo[0]);
                }

                throw $error;
            }

            if ($this->getCache()->isEnabled() && isset($cacheKey)) {
                $this->getCache()->save($cacheKey, $result);
            }
        }

        return $result;
    }

    protected function connect(): bool {
        if ($this->connected) {
            return true;
        }
        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->password, $this->options);

            /**
             * Set the PDO default attributes, if they not already set.
             */
            $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            /**
             * Try to fix MySQL character encoding problems.
             * MySQL < 5.5 does not support proper 4 byte unicode but they
             * seem to have added it with version 5.5 under a different label: utf8mb4.
             * We try to select the best possible charset based on your version data.
             */
            if (($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) === 'mysql') {
                $encoding = ((floatval($this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION))) >= 5.5) ? 'utf8mb4' : 'utf8';
                $this->pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, value: "SET NAMES $encoding"); //on every re-connect
                $this->pdo->exec("SET NAMES $encoding");                                              //right now
            }

            $this->connected = true;
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }
        return $this->connected;
    }

    protected function disconnect() {
        $this->pdo       = null;
        $this->connected = false;
    }

    // protected function __prepare(string $sql, array $params = [], array $options = []) {
    //     $result = $this->getPDO()->prepare($sql, $options);
    //     if ($result instanceof \PDOStatement  && count($params)) {
    //         $this->__bindParams($result, $params);
    //     }

    //     return $result;
    // }

    // protected function __bindParams(\PDOStatement $statement, array $params = []) {
    //     foreach ($params as $key => &$value) {
    //         if (is_integer($key)) {
    //             if ($value === NULL) {
    //                 $statement->bindValue($key + 1, null, PDO::PARAM_NULL);
    //             } else {
    //                 $statement->bindParam($key + 1, $value, $this->__getParamType($value));
    //             }
    //         } else {
    //             if ($value === NULL) {
    //                 $statement->bindValue($key, null, PDO::PARAM_NULL);
    //             } else {
    //                 $statement->bindParam($key, $value, $this->__getParamType($value));
    //             }
    //         }
    //     }
    // }

    // protected function __getParamType(mixed $value): int {
    //     if ($value === NULL) {
    //         return PDO::PARAM_NULL;
    //     } elseif (is_bool($value)) {
    //         return PDO::PARAM_BOOL;
    //     } elseif (is_int($value)) {
    //         return PDO::PARAM_INT;
    //     } else {
    //         return PDO::PARAM_STR;
    //     }
    // }

    // protected function __execute(string $sql, array $params = [], array $options = []) {
    //     try {
    //         $statement = $this->__prepare($sql, $options);
    //         if (count($params)) {
    //             $this->__bindParams($statement, $params);
    //         }
    //         $statement->execute();

    //         return $statement;
    //     } catch (\PDOException $e) {
    //         $exception = new ESQLError($e->getMessage(), 0);
    //         $exception->setSQLState((string) $e->getCode());

    //         throw $exception;
    //     }
    // }

    private function beforeExecute() {
        $this->queryCounter++;

        // if ($this->logging == true) {
        //     $index = $this->queryCounter;
        //     $this->benchmark->start( 'query'.$index.'_execute' );
        // }
    }

    private function afterExecute() {
        // if ($this->logging == true) {
        //     $index = $this->queryCounter;
        //     $this->benchmark->stop( $key = 'query'.$index.'_execute' );

        //     $logMsg = [
        //       'id'            => $index,
        //       'sql'           => $this->getSql(),
        //       'elapsed'       => $this->benchmark->elapsed( $key ),
        //       'rows_affected' => $this->queryRowsAffected,
        //     ];

        //     /** Basic log */
        //     $this->logDebug( 'Database execute: '.join( ',', $logMsg ) );
        // }
    }

}
/** End of Database **/