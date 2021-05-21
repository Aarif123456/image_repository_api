<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Exception\{DebugPDOException, MissingParameterException, NoSuchFileException, PDOWriteException};
use ImageRepository\Model\{Database, User};
use ImageRepository\Model\FileManagement\FileManager;
use ImageRepository\Utils\Auth;

use function ImageRepository\Api\{isValidRequestVar, missingParameterExit};
use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\{AUTHORIZED_USER};

/**
 * @throws DebugPDOException
 * @throws PDOWriteException
 * @throws NoSuchFileException
 * @throws MissingParameterException
 */
function deleteFile(Database $db, Auth $auth, bool $debug) {
    if (!(isValidRequestVar('fileName') || isValidRequestVar('fileId'))) {
        missingParameterExit();
    }
    /* Set variables */
    $user = new User($auth->getCurrentUserInfo());
    $output = [];
    $file = getFileInformation($user, $db);
    if ($file !== null) $output['error'] = !FileManager::deleteFile($file, $user, $db, $debug);
    echo createQueryJSON($output);
}

safeApiRun(AUTHORIZED_USER, '/deleteFile');