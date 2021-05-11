<?php
/* Define the error handlers  */

declare(strict_types=1);

require_once __DIR__ . '/apiReturn.php';

/* constants */
const COMMAND_FAILED = 'Query failed to execute, ensure you use the correct values';
const FILE_ALREADY_EXISTS = 'File already exists';
const FILE_SIZE_LIMIT_EXCEEDED = 'Exceeded file size limit';
const INTERNAL_SERVER_ERROR = 'Something went wrong:(';
const INTERNAL_SERVER_ERROR_JSON = '{"error":"Something went wrong:("}';
const INVALID_FILE_FORMAT = 'Invalid file format.';
const MISSING_PARAMETERS = 'Missing value';
const NO_FILE_SENT = 'No file sent.';
const NO_FILE_SENT_JSON = '{"error":"No file sent."}';
const NO_ROWS_RETURNED_JSON = '{"error":"No rows were found in database"}';
const UNAUTHORIZED_NO_LOGIN_JSON = '{"error":"user is not logged in!"}';
const USER_NOT_ADMIN = 'User is not an admin.';

set_exception_handler('exitWithJsonExceptionHandler');
// set_error_handler('exitWithJsonExceptionHandler');
function exitWithJsonExceptionHandler(Throwable $e) {
    $errorMessage = createQueryJSON(['error' => (string)$e], INTERNAL_SERVER_ERROR_JSON);
    exit($errorMessage);
}

/*TODO: make classes of exception and then just handle their message in the view folder
Reference: https://www.php.net/manual/en/language.exceptions.php
*/
