<?php

declare(strict_types=1);
require_once __DIR__ . '/../common/CustomException.php';
/* Error handling for calls to the database */
const INVALID_ACCESS_TYPE = 'Invalid file access policy.';
const PHP_EXCEPTION = 'The following exception was thrown:';
const SQL_ERROR = 'The following SQL error was detected:';
const WRITE_QUERY_FAILED = 'Failed to update the database';
const INVALID_PROPERTY = 'This property has not been initialized properly';
/* getting back the result of query as a JSON file */
function getExecutedResult($stmt) {
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    return $rows;
}

/* Catch exception that are set by MYSQLI_REPORT_ALL  */
/**
 * @throws PDOException
 * @throws DebugPDOException
 */
function safeWriteQueries($stmt, $conn, $debug): bool {
    try {
        return $stmt->execute() && $stmt->closeCursor();
    } catch (Exception $e) {
        /* remove all queries from queue if error (undo) */
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        if ($debug) {
            throw new DebugPDOException($conn, $e);
        }
    }
    throw new PDOException(WRITE_QUERY_FAILED, 1);
}

class InvalidAccessException extends CustomException
{
    protected $message = INVALID_ACCESS_TYPE;

}

class InvalidPropertyException extends CustomException
{
    protected $message = INVALID_PROPERTY;

}

class DebugPDOException extends Exception
{
    public function __construct(PDO $conn, Exception $e, int $code = 1) {
        $output = PHP_EXCEPTION . $e;
        if (!empty($conn->errorCode())) {
            $output = sprintf('%s%s%s', SQL_ERROR, json_encode($conn->errorInfo()), $output);
        }
        parent::__construct($output, $code);
    }

}