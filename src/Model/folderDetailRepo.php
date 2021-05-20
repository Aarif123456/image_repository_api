<?php

declare(strict_types=1);
namespace ImageRepository\Model\FileManagement;

use ImageRepository\Model\User;
use PDO;

use function ImageRepository\Model\getExecutedResult;

function getFolderDetail(string $filePath, User $user, PDO $conn): array {
    $stmt = $conn->prepare(
        'SELECT * FROM files WHERE filePath=:filePath AND memberID=:id'
    );
    $stmt->bindValue(':filePath', $filePath);
    $stmt->bindValue(':id', $user->id);

    return getExecutedResult($stmt);
}