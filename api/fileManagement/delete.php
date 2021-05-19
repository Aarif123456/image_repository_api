<?php

declare(strict_types=1);
/* Imports */
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../repository/deleteFilesRepo.php';
require_once __DIR__ . '/../../repository/User.php';
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../validEndpoint.php';
require_once __DIR__ . '/fileInformation.php';
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

safeApiRun(AUTHORIZED_USER, 'deleteFile');