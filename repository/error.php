<?php

declare(strict_types=1);

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
 * @throws Exception
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
            throw new PDOException(debugException($e, $conn), 1);
        }
    }
    throw new Exception(WRITE_QUERY_FAILED, 1);
}

function debugException($e, $conn): string {
    $output = PHP_EXCEPTION . $e;
    if (!empty($conn->errorCode())) $output = sprintf('%s%s%s', SQL_ERROR, json_encode($conn->errorInfo()), $output);

    return $output;
}

function debugQuery($affectedRow, $success, $functionName): string {
    return nl2br(
        "FUNCTION $functionName: row affected = $affectedRow \n FUNCTION $functionName: successful = $success"
    );
}


class InvalidAccessException extends Exception {
    public function __construct(string $message = null, int $code = 0) {
        if (!$message) {
            throw new $this(get_class($this) . ': ' . INVALID_ACCESS_TYPE);
        }
        parent::__construct($message, $code);
    }

    public function __toString() {
        return get_class($this) . ': ' . $this->message;
    }
}


class InvalidPropertyException extends Exception {
    public function __construct(string $message = null, int $code = 0) {
        if (!$message) {
            throw new $this(get_class($this) . ': ' . INVALID_PROPERTY);
        }
        parent::__construct($message, $code);
    }

    public function __toString() {
        return get_class($this) . ': ' . $this->message;
    }
}