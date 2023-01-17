<?php

declare(strict_types=1);

namespace Indgy\Log\Loggers;


abstract class AbstractLogger
{
    /**
     * The logging levels, matches PSR3.
     * @var array
     */
    private $levels = [
        0 => 'emergency',
        1 => 'alert',
        2 => 'critical',
        3 => 'error',
        4 => 'warning',
        5 => 'notice',
        6 => 'info',
        7 => 'debug'
    ];
    /**
     * @var Integer The default logging level
     */
    protected $level = 6;
    /**
     * @var Boolean Defer flushing log until __destruct is called
     */
    protected $deferred = false;
    /**
     * @var Boolean Continue to pass event to other loggers
     */
    protected $cascade = true;
    /**
     * @var Array Contains the log messages before flushing
     */
    protected $logs = [];


    public function __construct(Array $config)
    {
        foreach ($config as $k=>$v) {
            $method = sprintf("set%s", ucfirst(strtolower($k)));
            if (method_exists($this, $method)) {
                $this->$method($v);
            }
        }
    }
    public function __destruct()
    {
        if ($this->deferred) {
            $this->flush();
        }
    }

    /**
     * Send log messages to destination
     *
     * @return void
     */
    protected function flush() : Void
    {
        
    }
    /**
     * Set the cascade flag, determines if logging stops here or passes to next logger
     *
     * @param Bool $bool
     * @return Self
     */
    public function setCascade(Bool $bool) : Self
    {
        $this->cascade = $bool;
        return $this;
    }
    /**
     * Set the deferred flag, if set the log messages are sent on shutdown, otherwise they go immediately
     *
     * @param Bool $bool
     * @return Self
     */
    public function setDeferred(Bool $bool) : Self
    {
        $this->deferred = $bool;
        return $this;
    }
    /**
     * Set the cascade flag, determines if logging stops here or passes to next logger
     *
     * @param Int|String $level The log level, either a level flag or string
     * @return Self
     */
    public function setLevel($level) : Self
    {
        if ( ! is_int($level)) {
            $level = $this->getLevelCode($level);
        }
        if ($level < 0 OR $level > 7) {
            throw new \InvalidArgumentException("Cannot set log level $level");
        }

        $this->level = $level;
        return $this;
    }
    /**
     * Returns the value of the cascade flag
     *
     * @return Bool
     */
    public function getCascade() : Bool
    {
        return $this->cascade;
    }
    /**
     * Returns the value of the deferred flag
     *
     * @return Bool
     */
    public function getDeferred() : Bool
    {
        return $this->deferred;
    }
    /**
     * Returns the log level
     *
     * @return Int
     */
    public function getLevel() : Int
    {
        return $this->level;
    }
    /**
     * Returns the int value of the level code
     *
     * @param String $level 
     * @return Int
     */
    public function getLevelCode(String $level) : Int
    {
        $code = array_search($level, $this->levels);
        if (false === $code) {
            throw new \InvalidArgumentException("Invalid log level $level");
        }

        return $code;
    }
    /**
     * Returns true if logs with level are handled in the logger
     *
     * @return Bool
     */
    public function handles(String $level) : Bool
    {
        return $this->level >= $this->getLevelCode($level);
    }
    /**
     * Accepts and logs a message
     *
     * @param String $level The log level
     * @param String $message The message to log
     * @param string $context Additional context to log
     * @return Self
     */
    public function log(String $level, String $message, $context=null) : Self
    {
        $this->logs[] = sprintf(
            "\n[%s] %s %s %s",
            $this->getMicroTimestamp(),
            strtoupper($level),
            $this->formatMessage($message, $context),
            $this->formatContext($context)
        );
        if (false === $this->deferred) {
            $this->flush();
        }

        return $this;
    }
    /**
     * Returns the current micro timestamp
     *
     * @return String The current microtimestamp
     */
    private function getMicroTimestamp() : String
    {
        $mtime = microtime(true);
        $micro = sprintf("%06d", ($mtime - floor($mtime)) * 1000000);

        return date('Y-m-d H:i:s.'.$micro, (int) $mtime);
    }
    /**
     * Formats a log message as explicitly as possible
     *
     * @param String $message 
     * @param Mixed $context 
     * @return String
     */
    private function formatMessage(String $message, $context) : String
    {
        if ($context instanceof \Throwable) {
            return sprintf("%s: '%s' in '%s' on line %d", $message, $context->getMessage(), $context->getFile(), $context->getLine());
        }
        return $message;
    }
    /**
     * Formats the provided context as a string for storage
     *
     * @param Mixed $context 
     * @return String
     */
    private function formatContext($context=null) : String
    {
        // TODO allow formatters to be plugged in by type
        if (is_null($context)) {
            return "";
        }
        if ($context instanceof \Throwable) {
            return "\n".$context->getTraceAsString();
        }
        if (is_object($context)) {
            if (method_exists($context, 'toString')) {
                return $context->toString();
            }
            if (method_exists($context, 'toArray')) {
                return json_encode($context->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            }
            return json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        }
        //TODO handle JSONSerializable

        return json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

    }
}