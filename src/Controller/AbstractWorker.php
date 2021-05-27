<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

use Exception;
use ImageRepository\Exception\DebugPDOException;
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
            ErrorHandler::exitWithErrorJson('Failed to setup database');
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
        } catch (DebugPDOException $e) {
            if (!empty($this->db ?? null) && !empty($this->db->errorCode())) {
                ErrorHandler::exitWithErrorJson(sprintf('%s%s', $this->translator->SQL_ERROR,
                    json_encode($this->db->errorInfo())));
            }
        } catch (Exception $e) {
            ErrorHandler::printErrorJson($this->translator, $e);
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
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
    }

    private static function formatJsonInput() {
        if (empty($_REQUEST)) {
            $_REQUEST = json_decode(file_get_contents('php://input'), true);
            $_POST = json_decode(file_get_contents('php://input'), true);
        }
    }

    abstract public function run();

}