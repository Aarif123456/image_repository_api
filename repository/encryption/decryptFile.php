<?php
declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';
require_once __DIR__ . '/callApi.php';

function getFileDecrypted($encryptedFile, $user, $conn, $debug = false) {
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];
    $privateKey = getUserKey($user, $conn);
    
    return decrypt($publicKey, $privateKey, $encryptedFile, $debug);
}


