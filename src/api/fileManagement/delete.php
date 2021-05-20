<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Exception\{DebugPDOException, MissingParameterException, NoSuchFileException, PDOWriteException};
use ImageRepository\Model\{Database, User};

use function ImageRepository\Api\{isValidRequestVar, missingParameterExit};
use function ImageRepository\Model\FileManagement\deleteImage;
use function ImageRepository\Utils\getCurrentUserInfo;
use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\{AUTHORIZED_USER};

/**
 * @throws DebugPDOException
 * @throws PDOWriteException
 * @throws NoSuchFileException
 * @throws MissingParameterException
 */
function deleteFile(Database $db, bool $debug) {
    if (!(isValidRequestVar('fileName') || isValidRequestVar('fileId'))) {
        missingParameterExit();
    }
    /* Set variables */
    $user = new User(getCurrentUserInfo($db));
    $output = [];
    $file = getFileInformation($user, $db);
    if ($file !== null) $output['error'] = !deleteImage($file, $user, $db, $debug);
    echo createQueryJSON($output);
}

safeApiRun(AUTHORIZED_USER, '/deleteFile');