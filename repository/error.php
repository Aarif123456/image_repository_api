<?php

declare(strict_types=1);

/* Error handling for calls to the database */

const INVALID_ACCESS_TYPE = 'Invalid file access policy.';
const MISSING_PARAMETER_FOR_USER_TYPE = 'Missing required parameter for selected user type';
const PHP_EXCEPTION = 'The following exception was thrown:';
const SQL_ERROR = 'The following SQL error was detected:';
const WRITE_QUERY_FAILED = 'Failed to update the database';

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
            throw new Exception(debugException($e, $conn), 1);
        }
    }
    throw new Exception(WRITE_QUERY_FAILED, 1);
}

/**
 * @throws Exception
 */
function safeUpdateQueries($stmt, $conn, $debug): int {
    try {
        if ($stmt->execute()) {
            $num = $stmt->rowCount();
            $stmt->closeCursor();

            return $num;
        }
    } catch (Exception $e) {
        /* remove all queries from queue if error (undo) */
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        if ($debug) {
            throw new Exception(debugException($e, $conn), 1);
        }
    }
    throw new Exception(WRITE_QUERY_FAILED, 1);
}

/**
 * @throws Exception
 */
function safeInsertQueries($stmt, $conn, $debug): int {
    try {
        if ($stmt->execute()) {
            $num = $conn->lastInsertId();
            $stmt->closeCursor();

            return $num;
        }
    } catch (Exception $e) {
        /* remove all queries from queue if error (undo) */
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        if ($debug) {
            throw new Exception(debugException($e, $conn), 1);
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