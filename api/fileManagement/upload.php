<?php

declare(strict_types=1);
/* Imports */
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../repository/File.php';
require_once __DIR__ . '/../../repository/User.php';
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../validEndpoint.php';
require_once __DIR__ . '/fileProcessor.php';
/**
 * @throws FileAlreadyExistsException
 * @throws EncryptedFileNotCreatedException
 * @throws SqlCommandFailedException
 * @throws DebugPDOException
 * @throws FileNotSentException
 * @throws InvalidFileFormatException
 * @throws InvalidAccessException
 * @throws UnknownErrorException
 * @throws FileLimitExceededException
 * @throws EncryptionFailureException
 * @throws PDOWriteException
 * @throws MissingParameterException
 */
function upload(PDO $conn, bool $debug) {
    /* Set variables */
    $user = new User(getCurrentUserInfo($conn));
    $fileAccess = $_REQUEST['fileAccess'] ?? null;
    $filePath = $_REQUEST['filePath'] ?? '';
    $fileNames = $_REQUEST['fileNames'] ?? 'images';
    /* Make sure user uploaded a file*/
    if (!isValidFileVar($fileNames)) {
        missingParameterExit();
    }
    /* Create folder where user files will be stored */
    $userFolder = File::getUserFolder($filePath, $user->id);
    if (!file_exists($userFolder)) {
        mkdir($userFolder, 0777, true);
    }
    /*Create array to track if upload was successful */
    $uploadSuccess = [];
    $files = createFiles($fileNames);
    foreach ($files['error'] as $key => $value) {
        $file = new File([
            /*File names cannot have slashes because it would mess up paths -
            * and we want to clean the input cause we might want to display the filename later */
            'name' => $files['name'][$key],
            'size' => $files['size'][$key],
            'errorStatus' => $files['error'][$key],
            'location' => $files['tmp_name'][$key],
            /* NOTE: getting the type from the file is not always safe as it can be tampered. However, users 
            * only have access to their own files. So, we choose to ignore it  */
            /* TODO: try using - maybe set in create files function image_type_to_mime_type(exif_imagetype($file))*/
            'type' => $files['type'][$key],
            'path' => $filePath,
            'access' => $fileAccess,
            'ownerId' => $user->id
        ]);
        $uploadSuccess[$file->name] = processFile($file, $user, $conn);
    }
    echo createQueryJSON($uploadSuccess);
}

safeApiRun(AUTHORIZED_USER, 'upload');

