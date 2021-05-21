<?php

declare(strict_types=1);
namespace ImageRepository\api;

/* Define the strings the API will return  */
use ImageRepository\Exception\{MissingParameterException, StaticClassAssertionError};

/**
 * Class to make sure requests have the required arguments
 */
final class EndpointValidator
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function isValidFileVar(string $fileName): bool {
        return $_FILES[$fileName]['error'] ?? false;
    }

    public static function isValidPostVar(string $varName): bool {
        return $_POST[$varName] ?? false;
    }

    public static function isValidRequestVar(string $varName): bool {
        return $_REQUEST[$varName] ?? false;
    }

    /**
     * Make sure all required post variable is there
     *
     * @throws MissingParameterException
     */
    public static function checkMissingPostVars(array $keys) {
        if (!self::areValidPostVars($keys)) {
            self::missingParameterExit();
        }
    }

    public static function areValidPostVars(array $keys): bool {
        return self::validateArray($keys, $_POST);
    }

    public static function validateArray(array $keyList, array $targetArray): bool {
        /* We will get an array of keys - but they keys will be the values s we need to flip it*/
        $keys = array_flip($keyList);

        return count( /* The number of keys should be same size as our validated array */
                array_intersect_key( /* Test if we have the target keys in the a*/
                    array_filter($targetArray,  /* Don't count empty values */
                        function (string $val) {
                            return !empty($val);
                        }), $keys
                )
            ) === count($keys);
    }

    /**
     * @throws MissingParameterException
     */
    public static function missingParameterExit() {
        throw new MissingParameterException();
    }

    /**
     * Make sure all required request variable is there
     *
     * @throws MissingParameterException
     */
    public static function checkMissingRequestVars(array $keys) {
        if (!self::areValidRequestVars($keys)) {
            self::missingParameterExit();
        }
    }

    public static function areValidRequestVars(array $keys): bool {
        return self::validateArray($keys, $_REQUEST);
    }

}

