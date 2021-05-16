<?php
declare(strict_types=1);
require_once __DIR__ . '/encryptionConstants.php';
require_once __DIR__ . '/encryptionExceptionConstants.php';
/**
 * Modified: https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
 * @throws Exception
 */
function callApi(array $fields, array $options = [], bool $debug = false): array {
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    $defaults = [
        CURLOPT_URL => ENCRYPTION_ENDPOINT,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $fields,
        CURLOPT_RETURNTRANSFER => true,
    ];
    curl_setopt_array($ch, ($options + $defaults));
    // Make the the post request
    $result = (array)json_decode((string)curl_exec($ch), true);
    if (array_key_exists('Error', $result)) {
        throw new EncryptionFailureException($result['Error'], 1);
    }
    if (array_key_exists('error', $result)) {
        throw new EncryptionFailureException($result['error'], 1);
    }
    if ($debug) {
        echo 'result <br/>';
        var_dump($result);
        echo ' <br/>';
        echo 'Info <br/>';
        $info = curl_getinfo($ch);
        var_dump($info);
        echo ' <br/>';
        echo 'Error <br/>';
        $error = curl_error($ch);
        var_dump($error);
        echo ' <br/>';
    }

    return $result;
}

/* Return properties used for encryption*/
function generateProperties(string $type = 'a'): array {
    $args = [
        'method' => 'generateProperties',
        'type' => $type
    ];

    return callApi($args);
}

/* Returns: $publicKey:string, $masterKey: string */
function setup(string $properties = ENCRYPTION_PROPERTIES): array {
    $args = [
        'method' => 'setup',
        'properties' => $properties,
    ];

    return callApi($args);
}

/*Return: privateKey: string on success otherwise we get an error  */
function keygen(string $publicKey, string $masterKey, string $userAttributes, string $properties = ENCRYPTION_PROPERTIES): string {
    $args = [
        'method' => 'keygen',
        'properties' => $properties,
        'publicKey' => $publicKey,
        'masterKey' => $masterKey,
        'userAttributes' => $userAttributes,
    ];
    $result = callApi($args);
    if (array_key_exists('privateKey', $result)) return $result['privateKey'];
    throw new EncryptionFailureException('Keygen Failed!', 1);
}

/*Return: encryptedFile: string */
function encrypt(string $publicKey, string $policy, string $inputFile, string $properties = ENCRYPTION_PROPERTIES): string {
    $args = [
        'method' => 'encrypt',
        'properties' => $properties,
        'publicKey' => $publicKey,
        'policy' => $policy,
        'inputFile' => curl_file_create($inputFile, 'application/octet-stream', 'inputFile')
    ];
    $result = callApi($args);
    if (array_key_exists('encryptedFile', $result)) return base64_decode($result['encryptedFile']);
    throw new EncryptionFailureException('Encrypt Failed!', 1);
}

/*Return: decryptedFile: string */
function decrypt(string $publicKey, string $privateKey, string $encryptedFile, string $properties = ENCRYPTION_PROPERTIES): string {
    $args = [
        'method' => 'decrypt',
        'properties' => $properties,
        'publicKey' => $publicKey,
        'privateKey' => $privateKey,
        'encryptedFile' => curl_file_create($encryptedFile, 'application/octet-stream', 'encryptedFile'),
    ];
    $result = callApi($args);
    if (array_key_exists('decryptedFile', $result)) return base64_decode($result['decryptedFile']);
    throw new EncryptionFailureException('Decrypt Failed!', 1);
}

