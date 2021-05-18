<?php

declare(strict_types=1);
require_once __DIR__ . '/constants.php';
/* Reference: https://www.php.net/manual/en/language.exceptions.php */
/*TODO: use to create custom exceptions */
interface IException
{
    /* Protected methods inherited from Exception class */
    public function __construct($message = null, $code = 0);

    public function getMessage();       // Exception message

    public function getCode();          // User-defined Exception code

    public function getFile();          // Source filename

    public function getLine();          // Source line

    public function getTrace();         // An array of the backtrace()

    /* Overrideable methods inherited from Exception class */
    public function __toString();       // formatted string for display
}

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
        if (DEBUG) {
            return get_class($this) . " '" . $this->message . "' in " . $this->file . '(' . $this->line . ")\n"
                . "{$this->getTraceAsString()}";
        }

        return get_class($this) . ': ' . $this->message;
    }
}