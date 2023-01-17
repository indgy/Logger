<?php

declare(strict_types=1);

namespace Indgy\Log\Loggers;


class FileLogger extends AbstractLogger
{
    /**
     * @var string The path to write to
     */
    protected $path = null;


    public function setPath(String $path) : Self
    {
        if ( ! is_dir(dirname($path))) {
            throw new \RuntimeException(sprintf("Logger cannot store log files in '%s' as it is not a directory", dirname($path)));
        }
        $this->path = $path;

        return $this;
    }

    protected function flush() : Void
    {
        try {
            if (false === file_put_contents($this->path, join("", $this->logs), FILE_APPEND)) {
                throw new \RuntimeException("Logger encountered an error writing to log file at '$this->path'");
            }
            $this->logs = [];

        } catch (\Exception $e) {
            throw new \RuntimeException("Logger encountered an error writing to log file at '$this->path'");
        }
    }
}
