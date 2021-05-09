<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../common/authenticate.php';
require_once __DIR__ . '/../../repository/database.php';
require_once __DIR__ . '/../../repository/uploadFileRepo.php';
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
$user = (object)[
    'id' => getUserID($conn) ?? 0
];
$fileAccess = $_REQUEST['fileAccess'] ?? null;
$filePath = $_REQUEST['filePath'] ?? "userFiles/$user->id/";
$fileNames = $_REQUEST['fileNames'] ?? 'images';
/* Make sure user uploaded a file*/
if (!isValidFileVar($fileNames)) {
    exitWithError(MISSING_PARAMETER);
}

/* Create folder where user files will be stored */
if (!file_exists($filePath)) {
    mkdir($filePath, 0777, true);
}
/*Create array to track if upload was successful */
$uploadSuccess = [];

if (empty($_FILES)) exit(NO_FILE_SENT_JSON);
if (is_array($_FILES[$fileNames]['error']) || is_object($_FILES[$fileNames]['error'])) {
    foreach ($_FILES[$fileNames]['error'] as $key =>$value) {
        $file = (object)[
            'size' => $_FILES[$fileNames]['size'][$key],
            'errorStatus' => $_FILES[$fileNames]['error'][$key],
            'location' => $_FILES[$fileNames]['tmp_name'][$key],
             'type' => $_FILES[$fileNames]['type'][$key],
            /*File names cannot have slashes because it would mess up paths -
            * and we want to clean the input cause we might want to display the filename later */
            'name' => htmlentities(str_replace(['/', '\\'], '', basename($_FILES[$fileNames]['name'][$key]))),
            'access' => $fileAccess,
            'path' => $filePath
        ];
        $uploadSuccess[$file->name] = processFile($file, $user, $conn);
    }
} else {
    $file = (object)[
        'size' => $_FILES[$fileNames]['size'],
        'errorStatus' => $_FILES[$fileNames]['error'],
        'location' => $_FILES[$fileNames]['tmp_name'],
        'type' => $_FILES[$fileNames]['type'],
        /*File names cannot have slashes because it would mess up paths -
        * and we want to clean the input cause we might want to display the filename later */
        'name' => htmlentities(str_replace(['/', '\\'], '', basename($_FILES[$fileNames]['name']))),
        'access' => $fileAccess,
        'path' => $filePath
    ];
    $uploadSuccess[$file->name] = processFile($file, $user, $conn);
}


echo createQueryJSON($uploadSuccess, NO_FILE_SENT_JSON);
$conn = null;

function processFile($file, $user, $conn, $debug = DEBUG): array {
    try {
        checkFile($file);
        $output = ['success' => !empty(insertFile($file, $user, $conn, $debug))];
        if (!$output['success']) {
            throw new Exception(COMMAND_FAILED);
        }
    } catch (Exception $e) {
        $output = ['success' => false];
        echo $e;
        if ($debug) $output['error'] = $e;
    }

    return $output;
}