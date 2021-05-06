<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../common/authenticate.php';
require_once __DIR__ . '/../../repository/database.php';
require_once __DIR__ . '/../../repository/uploadFileRepo.php';
require_once __DIR__ . '/encryptFile.php';
require_once __DIR__ . '/fileValidation.php';

/* Set required header and session start */
requiredHeaderAndSessionStart();
$debug = DEBUG;

/* Connect to database */
$conn = getConnection();

/* Make sure user is logged in */
if (!(checkSessionInfo() && validateUser($conn))) {
    redirectToLogin();
}
$user = (object)[
    'ID' => getUserID($conn)
];


/* Set variables */
$fileAccess = $_REQUEST['fileAccess'] ?? PRIVATE_ACCESS;
$filePath = $_REQUEST['filePath'] ?? '';
$fileNames = $_REQUEST['fileNames'] ?? 'images';
$policy = getPolicy($fileAccess, $user);
/* Make sure user uploaded a file*/
if (isValidFileVar($fileNames)) {
    exit(MISSING_PARAMETERS);
}

/* Create folder where user files will be stored */
if (!file_exists($filePath)) {
    mkdir("userFiles/$user->ID/$filePath", 0777, true);
}
/*Create array to track if upload was successful */
$uploadSuccess = [];

foreach ($_FILES[$fileNames]['error'] as $key) {
    $file = (object)[
        'size' => $_FILES[$fileNames]['size'][$key],
        'errorStatus' => $_FILES[$fileNames]['error'][$key],
        'location' => $_FILES[$fileNames]['tmp_name'][$key],
        /*File names cannot have slashes because it would mess up paths - 
        * and we want to clean the input cause we might want to display the filename later */
        'name' => htmlentities(str_replace(['/', '\\'], '', basename($_FILES[$fileNames]['name']))),
        'access' => $fileAccess,
        'path' => $filePath
    ];

    checkFile($file);
    /* Store uploaded file*/
    $uploadSuccess[$file->name] = encryptFile($file, $policy);
    if ($uploadSuccess[$file->name]) {
        /*Insert info into database */
        try {
            $fileID = insertFile($file, $user, $conn, $debug);
        } catch (Exception $e) {
            if($debug) $output = ['error' => $e];
        }
        /*If one query fails we exit */
        if (empty($fileID)) {
            exit(COMMAND_FAILED);
        }
    }
}

echo createQueryJSON($uploadSuccess, NO_FILE_SENT);

$conn = null;
