<?php

declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';
require_once __DIR__ . '/callApi.php';
require_once __DIR__ . 'encryptFile.php';

/**
 * @param FileLocationInfo $file
 * @param string $privateKey
 * @param PDO $conn
 * @param false $debug
 * @return string
 * @throws Exception
 */
function getFileDecrypted(FileLocationInfo $file, string $privateKey, PDO $conn, bool $debug = false): string {
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];
    $encryptedFileLocation = getEncryptedFileLocation($file);
    $encryptedFile = file_get_contents($encryptedFileLocation);

    return decrypt($publicKey, $privateKey, $encryptedFile);
}


