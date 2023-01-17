<?php

declare(strict_types=1);

namespace Indgy\Log;

use Psr\Log\AbstractLogger;

/**
 * Logger multiplexer, this class instantiates one or more Loggers and 
 * dispatches events to each one via the log() method
 *
 * @package Logger
 * @author indgy
 */
class Logger extends AbstractLogger
{
    /**
     * @var Array<Int,AbstractLogger> Holds the Logger instances which are sorted by level
     */
    protected $loggers = [];


    /**
     * Configures the loggers as specified in the config
     *
     * @param Array<String,Mixed> $configs An Array of Logger configurations
     */
    public function __construct(Array $configs=[])
    {
        foreach ($configs as $config) {
            $this->loggers[] = new $config['class']($config);
        }
        $this->sortLoggers();
    }
    /**
     * Log an event, this will pass the event to each handler
     *
     * @param String $level 
     * @param String $message 
     * @param Array<Mixed,Mixed> $context 
     * @return void
     */
    public function log($level, $message, $context=[]) : Void
    {
        foreach ($this->loggers as $logger) {
            if ($logger->handles($level)) {
                $logger->log($level, $message, $context);
            }
            if (false === $logger->getCascade()) {
                break;
            }
        }
    }
    /**
     * System is unusable.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function emergency($message, array $context=[]) : Void
    {
        $this->log('emergency', $message, $context);
    }
    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function alert($message, array $context=[]) : Void
    {
        $this->log('alert', $message, $context);
    }
    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function critical($message, array $context=[]) : Void
    {
        $this->log('critical', $message, $context);
    }
    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function error($message, array $context=[]) : Void
    {
        $this->log('error', $message, $context);
    }
    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function warning($message, array $context=[]) : Void
    {
        $this->log('warning', $message, $context);
    }
    /**
     * Normal but significant events.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function notice($message, array $context=[]) : Void
    {
        $this->log('notice', $message, $context);
    }
    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function info($message, array $context=[]) : Void
    {
        $this->log('info', $message, $context);
    }
    /**
     * Detailed debug information.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function debug($message, array $context=[]) : Void
    {
        $this->log('debug', $message, $context);
    }
    /**
     * Sorts the loggers by level
     *
     * @return void
     */
    private function sortLoggers() : Void
    {
        usort($this->loggers, function ($a, $b) {
            return $a->getLevel() > $b->getLevel();
        });
    }
}