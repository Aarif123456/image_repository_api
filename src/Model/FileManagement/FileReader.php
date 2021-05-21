<?php

declare(strict_types=1);
namespace ImageRepository\Model\FileManagement;

/* Get information about the image */
use ImageRepository\Exception\{EncryptionFailureException, NoSuchFileException, StaticClassAssertionError};
use ImageRepository\Model\{Database, EncryptionKeyReader, FileLocationInfo, User};
use ImageRepository\Model\Encryption\FileDecrypter;

/**
 * Decrypt file and get back bytes
 */
final class FileReader
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @throws EncryptionFailureException
     * @throws NoSuchFileException
     */
    public static function getFileBytes(FileLocationInfo $file, User $user, Database $db): string {
        $privateKey = EncryptionKeyReader::userKey($user, $db);
        $publicKey = EncryptionKeyReader::publicKey($db);

        return FileDecrypter::run($file, $privateKey, $publicKey);
    }

    public static function getFileMime(FileLocationInfo $file, User $user, Database $db): string {
        return self::getFileMetaData($file, $user, $db)['mime'];
    }

    public static function getFileMetaData(FileLocationInfo $file, User $user, Database $db): array {
        $sql = 'SELECT * FROM files WHERE fileName=:fileName AND filePath=:filePath AND memberID=:id';
        $params = [
            ':fileName' => $file->name,
            ':filePath' => $file->path,
            ':id' => $user->id
        ];

        return $db->read($sql, $params)[0] ?? [];
    }
}

