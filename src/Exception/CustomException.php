<?php

declare(strict_types=1);
namespace ImageRepository\Exception;

/* Reference: https://www.php.net/manual/en/language.exceptions.php */
use Exception;

abstract class CustomException extends Exception implements IException
{
    protected $message = null;                      // Exception message
    protected $code = 0;                            // User-defined exception code
    protected $file;                                // Source filename of exception
    protected $line;                                // Source line of exception

    public function __construct($message = null, $code = 0) {
        if (!$message) {
            throw new $this($this->message ?? 'Unknown: ' . get_class($this));
        }
        parent::__construct($message, $code);
    }

    public function __toString() {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
            . "{$this->getTraceAsString()}";
    }
}