<?php

declare(strict_types=1);
namespace ImageRepository\Model\FileManagement;

use ImageRepository\Model\{Database, User};

function getFolderDetail(string $filePath, User $user, Database $db): array {
    $sql = 'SELECT * FROM files WHERE filePath=:filePath AND memberID=:id';
    $params = [':filePath' => $filePath, ':id' => $user->id];

    return $db->read($sql, $params);
}