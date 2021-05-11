<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../common/authenticate.php';
require_once __DIR__ . '/../../repository/database.php';
require_once __DIR__ . '/../../repository/uploadFileRepo.php';
require_once __DIR__ . '/../../repository/File.php';
require_once __DIR__ . '/../../repository/User.php';
require_once __DIR__ . '/fileValidation.php';

/* Set required header and session start */
requiredHeaderAndSessionStart();

$debug = DEBUG;
/* Connect to database */
$conn = getConnection();

/* Make sure user is logged in */
if (!validateUser($conn)) {
    redirectToLogin();
}

/* Set variables */
$userInfo = getCurrentUserInfo($conn);
$user = new User(getCurrentUserInfo($conn));

$fileAccess = $_REQUEST['fileAccess'] ?? null;
$filePath = $_REQUEST['filePath'] ?? "userFiles/$user->id/";
$fileNames = $_REQUEST['fileNames'] ?? 'images';
/* Make sure user uploaded a file*/
if (!isValidFileVar($fileNames)) {
    throw new Exception(MISSING_PARAMETERS);
}

/* Create folder where user files will be stored */
if (!file_exists($filePath)) {
    mkdir($filePath, 0777, true);
}
/*Create array to track if upload was successful */
$uploadSuccess = [];

if (empty($_FILES)) exit(NO_FILE_SENT_JSON);
if (is_array($_FILES[$fileNames]['error']) || is_object($_FILES[$fileNames]['error'])) {
    foreach ($_FILES[$fileNames]['error'] as $key => $value) {
        $file = new File([
            /*File names cannot have slashes because it would mess up paths -
            * and we want to clean the input cause we might want to display the filename later */
            'name' => htmlentities(str_replace(['/', '\\'], '', basename($_FILES[$fileNames]['name'][$key]))),
            'path' => $filePath,
            'size' => $_FILES[$fileNames]['size'][$key],
            'errorStatus' => $_FILES[$fileNames]['error'][$key],
            'location' => $_FILES[$fileNames]['tmp_name'][$key],
            /* NOTE: getting the type from the file is not always safe as it can be tampered. However, since users 
            * have to access to their own files, we don't really care  */
            'type' => $_FILES[$fileNames]['type'][$key],
            'access' => $fileAccess,
        ]);
        $uploadSuccess[$file->name] = processFile($file, $user, $conn);
    }
} else {
    /*Look at the above section for detail about the file fields. 
    * Here we handle the case where the user only uploads one file */
    $file = new File([
        'name' => htmlentities(str_replace(['/', '\\'], '', basename($_FILES[$fileNames]['name']))),
        'size' => $_FILES[$fileNames]['size'],
        'errorStatus' => $_FILES[$fileNames]['error'],
        'location' => $_FILES[$fileNames]['tmp_name'],
        'type' => $_FILES[$fileNames]['type'],
        'access' => $fileAccess,
        'path' => $filePath
    ]);
    $uploadSuccess[$file->name] = processFile($file, $user, $conn);
}


echo createQueryJSON($uploadSuccess, NO_FILE_SENT_JSON);
$conn = null;

function processFile(File $file, User $user, PDO $conn, bool $debug = DEBUG): array {
    try {
        checkFile($file);
        $output = ['success' => !empty(insertFile($file, $user, $conn, $debug))];
        if (!$output['success']) {
            throw new Exception(COMMAND_FAILED);
        }
        /*TODO: if we have successfully encrypted our file then remove them temporary non encrypted version */
        /* unlink($file->location); // Remove temp file */
    } catch (Exception $e) {
        $output = ['success' => false];
        $output['error'] = $e->getMessage();
    }

    return $output;
}