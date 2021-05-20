<?php

declare(strict_types=1);
namespace App\Api;

/* Define the strings the API will return  */
use App\Views\MissingParameterException;

function isValidFileVar(string $fileName): bool {
    return $_FILES[$fileName]['error'] ?? false;
}

function isValidPostVar(string $varName): bool {
    return $_POST[$varName] ?? false;
}

function isValidRequestVar(string $varName): bool {
    return $_REQUEST[$varName] ?? false;
}

function areValidPostVars(array $keys): bool {
    return validateArray($keys, $_POST);
}

function areValidRequestVars(array $keys): bool {
    return validateArray($keys, $_REQUEST);
}

/**
 * Make sure all required post variable is there
 *
 * @throws MissingParameterException
 */
function checkMissingPostVars(array $keys) {
    if (!areValidPostVars($keys)) {
        missingParameterExit();
    }
}

/**
 * Make sure all required request variable is there
 *
 * @throws MissingParameterException
 */
function checkMissingRequestVars(array $keys) {
    if (!areValidRequestVars($keys)) {
        missingParameterExit();
    }
}

function validateArray(array $keyList, array $targetArray): bool {
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
function missingParameterExit() {
    throw new MissingParameterException();
}
