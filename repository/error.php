<?php

declare(strict_types=1);
require_once __DIR__ . '/../common/CustomException.php';
/* Error handling for calls to the database */
/* Getting back the result of query as a JSON file */
function getExecutedResult($stmt) {
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    return $rows;
}

/**
 * Catch exception that are set by MYSQLI_REPORT_ALL
 *
 * @throws PDOException
 * @throws DebugPDOException
 * @throws PDOWriteException
 */
function safeWriteQueries($stmt, $conn, $debug): bool {
    try {
        return $stmt->execute() && $stmt->closeCursor();
    } catch (PDOException $e) {
        /* remove all queries from queue if error (undo) */
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        if ($debug) {
            throw new DebugPDOException();
        }
    }
    throw new PDOWriteException();
}

class InvalidAccessException extends CustomException
{
}
class InvalidPropertyException extends CustomException
{
}
class DebugPDOException extends CustomException
{
}
class PDOWriteException extends CustomException
{
}