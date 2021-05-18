<?php

/* Define the error handlers  */
declare(strict_types=1);
require_once __DIR__ . '/apiReturn.php';
require_once __DIR__ . '/../common/constants.php';
require_once __DIR__ . '/../common/CustomException.php';
/* constants */
const COMMAND_FAILED = 'Query failed to execute, ensure you use the correct values';
const FILE_ALREADY_EXISTS = 'File already exists';
const FILE_SIZE_LIMIT_EXCEEDED = 'Exceeded file size limit';
const INTERNAL_SERVER_ERROR = 'Something went wrong :(';
const INVALID_FILE_FORMAT = 'Invalid file format.';
const MISSING_PARAMETERS = 'Request is missing values. Please consult the documentation to ensure you are passing all the required arguments';
const NO_FILE_SENT = 'No file sent.';
const UNAUTHORIZED_NO_LOGIN = 'user is not logged in';
const USER_NOT_ADMIN = 'User is not an admin.';
/* During production we want a semi nice back-up in case we fail */
if (!DEBUG) {
    set_exception_handler('exitWithJsonExceptionHandler');
    set_error_handler('exitWithJsonExceptionHandler');
}
function exitWithJsonExceptionHandler(Throwable $e) {
    $errorMessage = createQueryJSON([
        'error' => true,
        'message' => (string)$e
    ]);
    exit($errorMessage);
}

class FileAlreadyExistsException extends CustomException
{
    protected $message = FILE_ALREADY_EXISTS;
}

class FileLimitExceededException extends CustomException
{
    protected $message = FILE_SIZE_LIMIT_EXCEEDED;
}

class FileNotSentException extends CustomException
{
    protected $message = NO_FILE_SENT;
}

class InvalidFileFormatException extends CustomException
{
    protected $message = INVALID_FILE_FORMAT;
}

class MissingParameterException extends CustomException
{
    protected $message = MISSING_PARAMETERS;
}
class UnknownErrorException extends CustomException
{
    protected $message = INTERNAL_SERVER_ERROR;
}

class UnauthorizedUserException extends CustomException
{
    protected $message = UNAUTHORIZED_NO_LOGIN;
}