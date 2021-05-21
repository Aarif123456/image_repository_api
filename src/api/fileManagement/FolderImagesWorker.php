<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Exception\StaticClassAssertionError;
use ImageRepository\Model\{Database, FileManagement\FolderReader, User};
use ImageRepository\Utils\Auth;
use ImageRepository\Views\JsonFormatter;

/**
 *
 */
final class FolderImagesWorker
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function run(Database $db, Auth $auth, bool $debug) {
        $user = new User($auth->getCurrentUserInfo());
        $filePath = $_REQUEST['filePath'] ?? '';
        $result = FolderReader::listFiles($filePath, $user, $db);
        JsonFormatter::printArray($result);
    }
}