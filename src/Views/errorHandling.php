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
    UnauthorizedAdminException,
    UnauthorizedUserException};
use PDOException;
use Throwable;

use function ImageRepository\Model\getConnection;
use function ImageRepository\Utils\{isUserAuthorized, unauthorizedExit};

use const ImageRepository\Utils\DEBUG;

function createErrorJson(string $message): string {
    return createQueryJSON([
        'error' => true,
        'message' => $message
    ]);
}

function printErrorJson(string $message) {
    echo createErrorJson($message);
}

function exitWithErrorJson(string $message) {
    exit(createErrorJson($message));
}

function errorJsonHandler(Throwable $e) {
    exitWithErrorJson((string)$e);
}

/* During production we want a semi nice back-up in case we fail */
if (!DEBUG) {
    set_exception_handler('/errorJsonHandler');
    set_error_handler('/errorJsonHandler');
}
function safeApiRun(int $authorizationLevel, callable $callback, array $args = []) {
    $translator = null;
    $conn = null;
    try {
        /* Set required header and session start */
        requiredHeaderAndSessionStart();
        /* Connect to database */
        $conn = getConnection();
        $translator = new Translator($conn);
        if (!isUserAuthorized($conn, $authorizationLevel)) {
            unauthorizedExit();
        }
        $debug = DEBUG;
        call_user_func_array($callback, array_merge([$conn, $debug], $args));
    } catch (EncryptedFileNotCreatedException $e) {
        printErrorJson($translator->ENCRYPTED_FILE_NOT_CREATED);
    } catch (FileAlreadyExistsException $e) {
        printErrorJson($translator->FILE_ALREADY_EXISTS);
    } catch (FileLimitExceededException $e) {
        printErrorJson($translator->FILE_SIZE_LIMIT_EXCEEDED);
    } catch (EncryptionFailureException $e) {
        printErrorJson($translator->INTERNAL_ENCRYPTION_FAILURE);
    } catch (InvalidAccessException $e) {
        printErrorJson($translator->INVALID_ACCESS_TYPE);
    } catch (InvalidFileFormatException $e) {
        printErrorJson($translator->INVALID_FILE_FORMAT);
    } catch (InvalidPropertyException $e) {
        printErrorJson($translator->INVALID_PROPERTY);
    } catch (MissingParameterException $e) {
        printErrorJson($translator->MISSING_PARAMETERS);
    } catch (FileNotSentException $e) {
        printErrorJson($translator->NO_FILE_SENT);
    } catch (NoSuchFileException $e) {
        printErrorJson($translator->NO_SUCH_FILE);
    } catch (SqlCommandFailedException $e) {
        printErrorJson($translator->COMMAND_FAILED);
    } catch (DebugPDOException $e) {
        if (!empty($conn ?? null) && !empty($conn->errorCode())) {
            printErrorJson(sprintf('%s%s', $translator->SQL_ERROR, json_encode($conn->errorInfo())));
        } else {
            throw new PDOException('Expected PDO object', 1);
        }
    } catch (PDOException $e) {
        printErrorJson($translator->PDO_ERROR);
    } catch (UnauthorizedUserException $e) {
        printErrorJson($translator->UNAUTHORIZED_NO_LOGIN);
    } catch (UnauthorizedAdminException $e) {
        printErrorJson($translator->USER_NOT_ADMIN);
    } catch (PDOWriteException $e) {
        printErrorJson($translator->WRITE_QUERY_FAILED);
    } catch (Exception $e) {
        printErrorJson($translator->INTERNAL_SERVER_ERROR);
    } catch (Throwable $e) {
        /* This meant we had a fatal an error instead of an exception so we don't have anyway to recover from it */
        exitWithErrorJson($translator->INTERNAL_SERVER_ERROR);
    } finally {
        $conn = null;
    }
}
