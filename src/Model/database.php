<?php

declare(strict_types=1);
namespace ImageRepository\Model;

use ImageRepository\Exception\{DebugPDOException, PDOWriteException};
use PDO;
use PDOException;
use PDOStatement;

require_once __DIR__ . '/loginConstants.php';
/* A proxy class for PDO that lets us control access */
final class Database
{
    public PDO $conn;

    public function __construct(
        string $db = DATABASE_NAME,
        string $username = DATABASE_USERNAME,
        string $password = DATABASE_PASSWORD,
        string $host = DATABASE_HOST,
        int $port = 3306,
        array $options = []
    ) {
        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $options = array_replace($defaultOptions, $options);
        $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
        try {
            $this->conn = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function read(string $sql, array $args = [], int $fetchMethod = PDO::FETCH_ASSOC): array {
        $stmt = $this->run($sql, $args);
        $rows = $stmt->fetchAll($fetchMethod);
        $stmt->closeCursor();

        return $rows;
    }

    public function run(string $sql, array $args = []): PDOStatement {
        if (empty($args)) return $this->conn->query($sql);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($args);

        return $stmt;
    }

    /**
     * Catch exception that are set by MYSQLI_REPORT_ALL
     *
     * @throws PDOException
     * @throws DebugPDOException
     * @throws PDOWriteException
     */
    public function write(string $sql, array $args = [], bool $debug = false): bool {
        $stmt = $this->run($sql, $args);
        try {
            return $stmt->execute() && $stmt->closeCursor();
        } catch (PDOException $e) {
            /* remove all queries from queue if error (undo) */
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($debug) {
                throw new DebugPDOException();
            }
        }
        throw new PDOWriteException();
    }

    public function beginTransaction(): bool {
        return $this->conn->beginTransaction();
    }

    public function commit(): bool {
        return $this->conn->commit();
    }

    public function errorCode(): ?string {
        return $this->conn->errorCode();
    }

    public function errorInfo(): array {
        return $this->conn->errorInfo();
    }

}

