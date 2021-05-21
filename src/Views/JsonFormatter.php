<?php

/* Define the error handlers  */
declare(strict_types=1);
namespace ImageRepository\Views;

use ImageRepository\Exception\StaticClassAssertionError;

/**
 * Class to format object and return them as JSON
 */
final class JsonFormatter
{
    function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function printArray(array $arr) {
        return self::jsonify($arr, []);
    }

    public static function jsonify($o, $defaultVal = null) {
        return json_encode($o ?? $defaultVal);
    }

}