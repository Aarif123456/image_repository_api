<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Exception\{DebugPDOException, MissingParameterException, NoSuchFileException, PDOWriteException};
use ImageRepository\Model\User;
use PDO;

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
function deleteFile(PDO $conn, bool $debug) {
    if (!(isValidRequestVar('fileName') || isValidRequestVar('fileId'))) {
        missingParameterExit();
    }
    /* Set variables */
    $user = new User(getCurrentUserInfo($conn));
    $output = [];
    $file = getFileInformation($user, $conn);
    if ($file !== null) $output['error'] = !deleteImage($file, $user, $conn, $debug);
    echo createQueryJSON($output);
}

safeApiRun(AUTHORIZED_USER, '/deleteFile');