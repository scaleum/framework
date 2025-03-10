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

namespace Scaleum\Console;

use Scaleum\Stdlib\Base\Hydrator;

/**
 * ConsoleOptions
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class ConsoleOptions extends Hydrator {
    public const OPT_REQUIRED     = 1 << 1;
    public const OPT_NOT_REQUIRED = 1 << 2;
    public const OPT_EMPTY        = 1 << 3;

    /**
     * Arguments
     * internal storage of the arguments to parse
     * @var array
     * @access private
     */
    private array $args = [];
    /**
     * Argument Count
     * The number of arguments passed to the class
     * @var integer
     * @access private
     */
    private int $args_count = 0;
    /**
     * Options
     * an array of acceptable short options (-o)
     * @var array
     * @access private
     */
    private array $opts = [];
    /**
     * Long Options
     * an array of acceptable long options (--option)
     * @var array
     */
    private array $opts_long = [];

    /**
     * Parsed options
     * @var array
     */
    private array $opts_parsed = [];

    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->parse();
    }

    public function get(string $option, mixed $default = null): mixed {
        if (isset($this->opts_parsed[$option])) {
            return $this->opts_parsed[$option];
        }

        return $default;
    }

    public function getAll(): array {
        return $this->opts_parsed;
    }

    public function getOpts(): array {
        return $this->opts;
    }

    public function getOptsLong(): array {
        return $this->opts_long;
    }

    /**
     * Parses the options passed to the class into an array and stores them locally
     * @return mixed
     */
    public function parse() {
        $this->opts_parsed = $opts_parsed = [];

        // Parse options
        foreach ([$this->opts, $this->opts_long] as $key => $opts) {
            if (is_array($opts)) {
                foreach ($opts as $opt) {
                    if (substr($opt, -2) == '::') {
                        $opts_parsed[substr($opt, 0, -2)] = self::OPT_NOT_REQUIRED;
                    } elseif (substr($opt, -1) == ':') {
                        $opts_parsed[substr($opt, 0, -1)] = self::OPT_REQUIRED;
                    } else {
                        $opts_parsed[$opt] = self::OPT_EMPTY;
                    }
                }
            }
        }

        $offset = (isset($_SERVER['argv'][0]) && basename($_SERVER['argv'][0]) == basename($_SERVER['SCRIPT_NAME'])) ? 0 : -1;

        // Check args
        if (empty($this->args)) {
            global $argv;
            $this->setArgs($argv);
        }

        // Check if we're done
        if (++$offset >= $this->args_count) {
            return null;
        }

        // Fill
        for ($i = 0; $i < $this->args_count; $i++) {

            // Check if it's a long option '--'
            if ($this->args[$i][0] == '-' && $this->args[$i][1] == '-') {
                // If there's an equal sign in the argument
                if (($pos = strpos($this->args[$i], '=')) !== false) {
                    $arg    = substr($this->args[$i], 2, ($pos - 2));
                    $argVal = substr($this->args[$i], $pos + 1);
                } else {
                    $arg = substr($this->args[$i], 2);
                    if ($this->args_count > ($i + 1)) {
                        $argVal = $this->args[$i + 1][0] == '-' ? null : $this->args[$i + 1];
                    } else {
                        $argVal = null;
                    }

                }
            } // Check if it's a short option '-' or '/'
            elseif (in_array($this->args[$i][0], ['-', '/'])) {
                $arg    = $this->args[$i][1];
                $argVal = strlen($this->args[$i]) == 2 ? (($this->args_count > ($i + 1) ? ($this->args[$i + 1][0] == '-' ? null : $this->args[$i + 1]) : null)) : substr($this->args[$i], 2);
            } // Option is not found
            else {
                continue;
            }

            // Is it in the approved list
            if (array_key_exists($arg, $opts_parsed)) {
                switch ($opts_parsed[$arg]) {
                case self::OPT_NOT_REQUIRED:
                    $this->opts_parsed[$arg] = $this->sanitizeOptionValue($argVal);
                    break;
                case self::OPT_REQUIRED:
                    if ($argVal !== null) {
                        $this->opts_parsed[$arg] = $this->sanitizeOptionValue($argVal);
                    }
                    break;
                case self::OPT_EMPTY:
                    $this->opts_parsed[$arg] = true;
                }
            } else {
                continue;
            }
        }

        return $this->opts_parsed;
    }

    public function setArgs(array $args): static
    {
        if (! empty($args)) {
            $this->args       = $args;
            $this->args_count = count($this->args);
        }

        return $this;
    }

    public function setOpts(array $opts): static
    {
        array_walk($opts, function (&$value) {$value = $this->sanitizeOptionValue($value);});
        $this->opts = $opts;

        return $this;
    }

    public function setOptsLong(array $opts): static
    {
        array_walk($opts, function (&$value) {$value = $this->sanitizeOptionValue($value);});
        $this->opts_long = $opts;

        return $this;
    }

    private function sanitizeOptionValue(array | string $val) {
        return preg_replace('/^[\-\=\s]+/', '', $val);
    }
}
/** End of ConsoleOptions **/