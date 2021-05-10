<?php
declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';
require_once __DIR__ . '/callApi.php';
require_once __DIR__ . '/encryptFile.php';

/**
 * @param $file
 * @param $user
 * @param $conn
 * @param false $debug
 * @return string
 * @throws Exception
 */
function getFileDecrypted($file, $user, $conn, $debug = false): string {
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];
    $privateKey = getUserKey($user, $conn);
    $encryptedFileLocation = getEncryptedFileLocation($file);
    $encryptedFile = file_get_contents($encryptedFileLocation);

    return decrypt($publicKey, $privateKey, $encryptedFile, $debug);
}


