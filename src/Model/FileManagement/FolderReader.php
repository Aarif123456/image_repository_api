<?php

declare(strict_types=1);
namespace ImageRepository\Model\FileManagement;

use ImageRepository\Exception\StaticClassAssertionError;
use ImageRepository\Model\{Database, User};

/**
 * Class handles getting information about a folder
 */
final class FolderReader
{
    function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function listFiles(string $filePath, User $user, Database $db): array {
        $sql = 'SELECT * FROM files WHERE filePath=:filePath AND memberID=:id';
        $params = [':filePath' => $filePath, ':id' => $user->id];

        return $db->read($sql, $params);
    }

    // TODO: public static function listSubfolders(string $filePath, User $user, Database $db){}
}