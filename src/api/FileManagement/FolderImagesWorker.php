<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\Exception\StaticClassAssertionError;
use ImageRepository\Model\{Database, FileManagement\FolderReader, User};
use ImageRepository\Utils\Auth;
use ImageRepository\Views\JsonFormatter;

/**
 * Class that handles logic to get images in folder
 */
final class FolderImagesWorker
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function run(Database $db, Auth $auth, bool $_debug) {
        $user = User::createFromAuth($auth);
        $folderPath = $_REQUEST['folderPath'] ?? '/';
        $result = FolderReader::listFiles($folderPath, $user, $db);
        JsonFormatter::printArray($result);
    }
}