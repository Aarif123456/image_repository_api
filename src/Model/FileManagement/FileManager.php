<?php

declare(strict_types=1);
namespace ImageRepository\Model\FileManagement;

use ImageRepository\Exception\{DebugPDOException, PDOWriteException, StaticClassAssertionError};
use ImageRepository\Model\{Database, File, FileLocationInfo, User};

/**
 * Encrypt a file, upload it and save a reference to it in the database
 */
final class FileManager
{
    function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @throws DebugPDOException
     * @throws PDOWriteException
     */
    public static function addFile(File $file, User $user, Database $db, bool $debug = false): bool {
        $sql = 'INSERT INTO files (memberID, filePath, fileName, fileSize, accessID, mime) VALUES (:memberID, :filePath, :fileName, :fileSize, :accessID, :mime)';
        $params = [
            ':memberID' => $user->id,
            ':filePath' => $file->path,
            ':fileName' => $file->name,
            ':fileSize' => $file->size,
            ':accessID' => $file->access,
            ':mime' => $file->type
        ];

        return $db->write($sql, $params, $debug);
    }

    /**
     * Function to delete file
     *
     * @throws DebugPDOException
     * @throws PDOWriteException
     */
    public static function deleteFile(FileLocationInfo $file, User $user, Database $db, bool $debug = false): bool {
        $sql = 'DELETE FROM files WHERE fileName=:fileName AND filePath=:filePath AND memberID=:id';
        $params = [
            ':fileName' => $file->name,
            ':filePath' => $file->path,
            ':id' => $user->id,
        ];
        $filePath = $file->getEncryptedFilePath();
        if (file_exists($filePath)) {
            /* If we delete from database then delete locally */
            if ($db->write($sql, $params, $debug)) return unlink($filePath);
        }

        return false;
    }

}