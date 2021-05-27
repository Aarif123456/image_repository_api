<?php

/* Define the error handlers  */
declare(strict_types=1);
namespace ImageRepository\Views;

use ImageRepository\Exception\{StaticClassAssertionError};
use ReflectionClass;
use ReflectionException;
use Throwable;

use const ImageRepository\Utils\DEBUG;

/**
 * Class to manage all the exception's response
 */
final class ErrorHandler
{
    private static array $customExceptionMapper = [
        'DebugPDOException' => 'PDO_ERROR',
        'DeleteFailedException' => 'FILE_DELETE_FAILED',
        'EncryptedFileNotCreatedException' => 'ENCRYPTED_FILE_NOT_CREATED',
        'EncryptionFailureException' => 'INTERNAL_ENCRYPTION_FAILURE',
        'FileAlreadyExistsException' => 'FILE_ALREADY_EXISTS',
        'FileLimitExceededException' => 'FILE_SIZE_LIMIT_EXCEEDED',
        'FileNotSentException' => 'NO_FILE_SENT',
        'InvalidAccessException' => 'INVALID_ACCESS_TYPE',
        'InvalidFileFormatException' => 'INVALID_FILE_FORMAT',
        'InvalidPropertyException' => 'INVALID_PROPERTY',
        'MissingParameterException' => 'MISSING_PARAMETERS',
        'NoSuchFileException' => 'NO_SUCH_FILE',
        'PDOException' => 'PDO_ERROR',
        'PDOWriteException' => 'WRITE_QUERY_FAILED',
        'SqlCommandFailedException' => 'COMMAND_FAILED',
        'UnauthorizedAdminException' => 'USER_NOT_ADMIN',
        'UnauthorizedUserException' => 'UNAUTHORIZED_NO_LOGIN'
    ];

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

    public static function printErrorJson(Translator $translator, Throwable $e) {
        echo self::createLocalizedErrorJson($translator, $e);
    }

    public static function createLocalizedErrorJson(Translator $translator, Throwable $e): string {
        return self::createErrorJson(self::createLocalizedError($translator, $e));
    }

    public static function createLocalizedError(Translator $translator, Throwable $e): string {
        $messageKey = 'INTERNAL_SERVER_ERROR';
        try {
            $re = new ReflectionClass(get_class($e));
            $messageKey = self::$customExceptionMapper[$re->getShortName()] ?? 'INTERNAL_SERVER_ERROR';
        } catch (ReflectionException $ignored) {
        }
        $message = $translator->dictionary[$messageKey] ?? 'Something went really wrong :(';
        if (DEBUG) $message .= '\n ' . $e;

        return $message;
    }

}
