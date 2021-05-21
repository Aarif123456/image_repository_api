<?php

/* Define the error handlers  */
declare(strict_types=1);
namespace ImageRepository\Views;

use Exception;
use ImageRepository\Exception\{DebugPDOException,
    EncryptedFileNotCreatedException,
    EncryptionFailureException,
    FileAlreadyExistsException,
    FileLimitExceededException,
    FileNotSentException,
    InvalidAccessException,
    InvalidFileFormatException,
    InvalidPropertyException,
    MissingParameterException,
    NoSuchFileException,
    PDOWriteException,
    SqlCommandFailedException,
    StaticClassAssertionError,
    UnauthorizedAdminException,
    UnauthorizedUserException};
use ImageRepository\Model\Database;
use ImageRepository\Utils\Auth;
use PDOException;
use Throwable;

use const ImageRepository\Utils\DEBUG;

require_once __DIR__ . '/apiReturn.php';

/**
 * Class to manage all the exception's response
 */
final class ErrorHandler
{
    function __construct() {
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

    public static function safeApiRun(int $authorizationLevel, callable $callback, array $args = []) {
        /* During production we want a semi nice back-up in case we fail */
        if (!DEBUG) {
            self::setErrorHandler();
        }
        $translator = null;
        $db = null;
        try {
            /* Set required header */
            setHeaders();
            /* Connect to database */
            $db = new Database();
            $translator = new Translator($db->conn);
            $auth = new Auth($db->conn);
            if (!$auth->isUserAuthorized($authorizationLevel)) {
                Auth::unauthorizedExit();
            }
            $debug = DEBUG;
            call_user_func_array($callback, array_merge([$db, $auth, $debug], $args));
        } catch (EncryptedFileNotCreatedException $e) {
            self::printErrorJson($translator->ENCRYPTED_FILE_NOT_CREATED);
        } catch (FileAlreadyExistsException $e) {
            self::printErrorJson($translator->FILE_ALREADY_EXISTS);
        } catch (FileLimitExceededException $e) {
            self::printErrorJson($translator->FILE_SIZE_LIMIT_EXCEEDED);
        } catch (EncryptionFailureException $e) {
            self::printErrorJson($translator->INTERNAL_ENCRYPTION_FAILURE);
        } catch (InvalidAccessException $e) {
            self::printErrorJson($translator->INVALID_ACCESS_TYPE);
        } catch (InvalidFileFormatException $e) {
            self::printErrorJson($translator->INVALID_FILE_FORMAT);
        } catch (InvalidPropertyException $e) {
            self::printErrorJson($translator->INVALID_PROPERTY);
        } catch (MissingParameterException $e) {
            self::printErrorJson($translator->MISSING_PARAMETERS);
        } catch (FileNotSentException $e) {
            self::printErrorJson($translator->NO_FILE_SENT);
        } catch (NoSuchFileException $e) {
            self::printErrorJson($translator->NO_SUCH_FILE);
        } catch (SqlCommandFailedException $e) {
            self::printErrorJson($translator->COMMAND_FAILED);
        } catch (DebugPDOException $e) {
            if (!empty($db ?? null) && !empty($db->errorCode())) {
                self::printErrorJson(sprintf('%s%s', $translator->SQL_ERROR, json_encode($db->errorInfo())));
            } else {
                throw new PDOException('Expected PDO object', 1);
            }
        } catch (PDOException $e) {
            self::printErrorJson($translator->PDO_ERROR);
        } catch (UnauthorizedUserException $e) {
            self::printErrorJson($translator->UNAUTHORIZED_NO_LOGIN);
        } catch (UnauthorizedAdminException $e) {
            self::printErrorJson($translator->USER_NOT_ADMIN);
        } catch (PDOWriteException $e) {
            self::printErrorJson($translator->WRITE_QUERY_FAILED);
        } catch (Exception $e) {
            self::printErrorJson($translator->INTERNAL_SERVER_ERROR);
        } catch (Throwable $e) {
            /* This meant we had a fatal an error instead of an exception so we don't have anyway to recover from it */
            self::exitWithErrorJson($translator->INTERNAL_SERVER_ERROR);
        }
    }

    public static function setErrorHandler() {
        set_exception_handler('self::errorJsonHandler');
        set_error_handler('self::errorJsonHandler');
    }

    public static function printErrorJson(string $message) {
        echo self::createErrorJson($message);
    }

}
