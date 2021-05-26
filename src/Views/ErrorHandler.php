<?php

/* Define the error handlers  */
declare(strict_types=1);
namespace ImageRepository\Views;

use ImageRepository\Exception\{StaticClassAssertionError};
use Throwable;

/**
 * Class to manage all the exception's response
 */
final class ErrorHandler
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function errorJsonHandler(Throwable $e) {
        self::exitWithErrorJson((string)$e);
    }

    public static function exitWithErrorJson(string $message) {
        exit(self::createErrorJson($message));
    }

    public static function createErrorJson(string $message): string {
        return JsonFormatter::jsonify([
            'error' => true,
            'message' => $message
        ]);
    }

    public static function setErrorHandler() {
        set_exception_handler('self::errorJsonHandler');
        set_error_handler('self::errorJsonHandler');
    }

    public static function printErrorJson(string $message) {
        echo self::createErrorJson($message);
    }

}
