<?php

declare(strict_types=1);
/* Define the strings the api will return  */
require_once __DIR__ . '/../views/errorHandling.php';
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

/* Make sure all required post variable is there */
function checkMissingPostVars(array $keys) {
    if (!areValidPostVars($keys)) {
        missingParameterExit();
    }
}

/* Make sure all required request variable is there */
function checkMissingRequestVars(array $keys) {
    if (!areValidRequestVars($keys)) {
        missingParameterExit();
    }
}

function validateArray(array $keys, array $targetArray): bool {
    return count( /* The number of keys should be same size as our validated array */
            array_intersect_key( /* Test if we have the target keys in the a*/
                array_filter($targetArray,  /* Don't count empty values */
                    function (string $val) {
                        return !empty($val);
                    }), $keys
            )
        ) === count($keys);
}

function missingParameterExit() {
    exitWithJsonExceptionHandler(new MissingParameterException());
}
