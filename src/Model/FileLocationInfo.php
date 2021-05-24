<?php

declare(strict_types=1);
namespace ImageRepository\Model;

use ImageRepository\Exception\NoSuchFileException;

class FileLocationInfo
{
    public string $name;
    public string $path;
    public string $realPath;

    public function __construct(array $properties = []) {
        $this->name = self::cleanFileName($properties['name']);
        $this->path = self::cleanFilePath($properties['path']);
        $ownerId = (int)$properties['ownerId'];
        /* Make sure user is contained to their folder */
        $this->realPath = self::getUserFolder($this->path, $ownerId) . "$this->name";
    }

    private static function cleanFileName(string $fileName): string {
        $weirdCharRemovedName = preg_replace('~[^a-zA-Z0-9_\-.]~', '', basename($fileName));
        $count = (substr_count($weirdCharRemovedName, '.') - 1);

        /* Remove all the dots except the last one */

        return preg_replace('~\.~', '', $weirdCharRemovedName, $count);
    }

    /* Make sure we have the file location */
    private static function cleanFilePath(string $filePath): string {
        $path = (string)preg_replace('~[^a-zA-Z0-9_\\\/\-]~', '', $filePath);
        /* Make sure path ends with a slash */
        if (empty($path) || mb_substr($path, -1) !== '/') $path .= '/';

        return $path;
    }

    /* Handle getting the actual file path for the user */
    public static function getUserFolder(string $filePath, int $ownerId): string {
        return "userFiles/$ownerId" . self::cleanFilePath($filePath);
    }

    /**
     * @throws NoSuchFileException
     */
    public static function createFromId(int $fileId, User $user, Database $db): FileLocationInfo {
        $sql = 'SELECT fileName as \'name\', filePath as \'path\', memberID as \'ownerId\' FROM files WHERE fileID=:fileId AND memberID=:id';
        $params = [
            ':fileId' => $fileId,
            ':id' => $user->id
        ];
        $rows = $db->read($sql, $params);
        if (empty($rows)) {
            throw new NoSuchFileException();
        }

        return new FileLocationInfo($rows[0]);
    }

    public function getEncryptedFilePath(): string {
        return "$this->realPath.Encrypted";
    }

}
