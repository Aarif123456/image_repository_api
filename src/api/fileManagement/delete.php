<?php

declare(strict_types=1);
namespace App\Api\FileManagement;

/* Imports */
use App\Model\{DebugPDOException, PDOWriteException, User};
use App\Model\Encryption\NoSuchFileException;
use App\Views\MissingParameterException;
use PDO;

use function App\Api\{isValidRequestVar, missingParameterExit};
use function App\Model\FileManagement\deleteImage;
use function App\Utils\getCurrentUserInfo;
use function App\Views\{createQueryJSON, safeApiRun};

use const App\Utils\{AUTHORIZED_USER};

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