<?php

declare(strict_types=1);
namespace ImageRepository\Exception;

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