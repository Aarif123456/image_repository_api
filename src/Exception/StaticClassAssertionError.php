<?php

declare(strict_types=1);
namespace ImageRepository\Exception;

use AssertionError;
use Throwable;

class StaticClassAssertionError extends AssertionError
{
    public function __construct($message = '', $code = 0, Throwable $previous = null) {
        if (empty($message)) throw new $this('Invalid use of static class', $code, $previous);
        parent::__construct($message, $code, $previous);
    }
}