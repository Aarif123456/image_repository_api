<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

use ImageRepository\Exception\{NoSuchFileException, StaticClassAssertionError};
use ImageRepository\Model\{Database, FileLocationInfo, User};

final class FileLocationInfoFactory
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @throws NoSuchFileException
     */
    public static function createFromApiData(User $user, Database $db): FileLocationInfo {
        $filePath = $_REQUEST['filePath'] ?? '/';
        $fileName = $_REQUEST['fileName'] ?? '';
        $fileId = $_REQUEST['fileId'] ?? null;
        $file = null;
        if (!empty($fileId)) {
            $file = FileLocationInfo::createFromId($fileId, $user, $db);
        } elseif (!empty($fileName)) {
            $file = new FileLocationInfo([
                'name' => $fileName,
                'path' => $filePath,
                'ownerId' => $user->id
            ]);
        }

        return $file;
    }
}
