<?php

declare(strict_types=1);
/* Imports */
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../repository/File.php';
require_once __DIR__ . '/../../repository/folderDetailRepo.php';
require_once __DIR__ . '/../../repository/User.php';
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../validEndpoint.php';
/* TODO: remove "get" from file name */
function folderDetail(PDO $conn, bool $debug) {
    $user = new User(getCurrentUserInfo($conn));
    $filePath = $_REQUEST['filePath'] ?? '';
    $result = getFolderDetail($filePath, $user, $conn);
    echo createQueryJSON($result);
}

safeApiRun(AUTHORIZED_USER, 'folderDetail');