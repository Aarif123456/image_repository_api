<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

use Exception;
use ImageRepository\Exception\{DebugPDOException,
    DeleteFailedException,
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
use ImageRepository\Model\Database;
use ImageRepository\Utils\Auth;
use ImageRepository\Views\ErrorHandler;
use ImageRepository\Views\Translator;
use PDOException;
use Throwable;

use const ImageRepository\Utils\DEBUG;

abstract class AbstractWorker
{
    protected Translator $translator;
    protected Database $db;
    protected Auth $auth;
    protected bool $debug;

    /* Let client send JSON formatted requests */
    public function __construct(Database $db = null, $debug = DEBUG) {
        try {
            $this->debug = $debug;
            /* Connect to database */
            $this->db = $db ?? new Database();
            $this->translator = new Translator($this->db->conn);
            $this->auth = new Auth($this->db->conn);
        } catch (PDOException $e) {
            header('Content-Type: application/json; charset=UTF-8');
            ErrorHandler::printErrorJson('Failed to setup database');
        }
    }

    /* Required header */

    final public function safeRun(int $authorizationLevel) {
        try {
            /* Set required header */
            self::setHeader();
            /* Get input in JSON form*/
            self::formatJsonInput();
            if (!$this->auth->isUserAuthorized($authorizationLevel)) {
                Auth::unauthorizedExit();
            }
            $this->run();
        } catch (DeleteFailedException $e) {
            ErrorHandler::printErrorJson($this->translator->FILE_DELETE_FAILED);
        } catch (EncryptedFileNotCreatedException $e) {
            ErrorHandler::printErrorJson($this->translator->ENCRYPTED_FILE_NOT_CREATED);
        } catch (FileAlreadyExistsException $e) {
            ErrorHandler::printErrorJson($this->translator->FILE_ALREADY_EXISTS);
        } catch (FileLimitExceededException $e) {
            ErrorHandler::printErrorJson($this->translator->FILE_SIZE_LIMIT_EXCEEDED);
        } catch (EncryptionFailureException $e) {
            ErrorHandler::printErrorJson($this->translator->INTERNAL_ENCRYPTION_FAILURE);
        } catch (InvalidAccessException $e) {
            ErrorHandler::printErrorJson($this->translator->INVALID_ACCESS_TYPE);
        } catch (InvalidFileFormatException $e) {
            ErrorHandler::printErrorJson($this->translator->INVALID_FILE_FORMAT);
        } catch (InvalidPropertyException $e) {
            ErrorHandler::printErrorJson($this->translator->INVALID_PROPERTY);
        } catch (MissingParameterException $e) {
            ErrorHandler::printErrorJson($this->translator->MISSING_PARAMETERS);
        } catch (FileNotSentException $e) {
            ErrorHandler::printErrorJson($this->translator->NO_FILE_SENT);
        } catch (NoSuchFileException $e) {
            ErrorHandler::printErrorJson($this->translator->NO_SUCH_FILE);
        } catch (SqlCommandFailedException $e) {
            ErrorHandler::printErrorJson($this->translator->COMMAND_FAILED);
        } catch (DebugPDOException $e) {
            if (!empty($this->db ?? null) && !empty($this->db->errorCode())) {
                ErrorHandler::printErrorJson(sprintf('%s%s', $this->translator->SQL_ERROR,
                    json_encode($this->db->errorInfo())));
            } else {
                throw new PDOException('Expected PDO object', 1);
            }
        } catch (PDOException $e) {
            ErrorHandler::printErrorJson($this->translator->PDO_ERROR);
        } catch (UnauthorizedUserException $e) {
            ErrorHandler::printErrorJson($this->translator->UNAUTHORIZED_NO_LOGIN);
        } catch (UnauthorizedAdminException $e) {
            ErrorHandler::printErrorJson($this->translator->USER_NOT_ADMIN);
        } catch (PDOWriteException $e) {
            ErrorHandler::printErrorJson($this->translator->WRITE_QUERY_FAILED);
        } catch (Exception $e) {
            ErrorHandler::printErrorJson($this->translator->INTERNAL_SERVER_ERROR);
        } catch (Throwable $e) {
            /* This meant we had a fatal an error instead of an exception so we don't have anyway to recover from it */
            ErrorHandler::exitWithErrorJson($this->translator->INTERNAL_SERVER_ERROR);
        }
    }

    private static function setHeader() {
        header('Access-Control-Allow-Origin: https://abdullaharif.tech');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: X-Requested-With, X-PINGOTHER, content-type');
        header('Content-Type: application/json; charset=UTF-8'); // most endpoints application will always return JSON
        // header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
        // header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    private static function formatJsonInput() {
        if (empty($_REQUEST)) {
            $_REQUEST = json_decode(file_get_contents('php://input'), true);
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
    }

    abstract public function run();

}