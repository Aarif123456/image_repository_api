<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

use ImageRepository\Model\{FileManagement\FolderReader, User};
use ImageRepository\Views\JsonFormatter;

/**
 * Class that handles logic to get images in folder
 */
final class FolderImagesWorker extends AbstractWorker
{
    public function run() {
        $user = User::createFromAuth($this->auth);
        $folderPath = $_REQUEST['folderPath'] ?? '/';
        $result = FolderReader::listFiles($folderPath, $user, $this->db);
        JsonFormatter::printArray($result);
    }
}