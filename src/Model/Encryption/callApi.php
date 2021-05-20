<?php

declare(strict_types=1);
namespace ImageRepository\Model\Encryption;

use ImageRepository\Exception\EncryptionFailureException;

require_once __DIR__ . '/encryptionConstants.php';
/**
 * Modified: https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
 *
 * @throws EncryptionFailureException
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

/**
 * @param string $type
 * @return array $properties used for encryption
 * @throws EncryptionFailureException
 */
function generateProperties(string $type = 'a'): array {
    $args = [
        'method' => 'generateProperties',
        'type' => $type
    ];

    return callApi($args);
}

/**
 * @param string $properties
 * @return array $publicKey:string, $masterKey: string
 * @throws EncryptionFailureException
 */
function setup(string $properties = ENCRYPTION_PROPERTIES): array {
    $args = [
        'method' => 'setup',
        'properties' => $properties,
    ];

    return callApi($args);
}

/**
 * @param string $publicKey
 * @param string $masterKey
 * @param string $userAttributes
 * @param string $properties
 * @return string $privateKey: string on success otherwise we get an error
 * @throws EncryptionFailureException
 */
function keygen(
    string $publicKey,
    string $masterKey,
    string $userAttributes,
    string $properties = ENCRYPTION_PROPERTIES
): string {
    $args = [
        'method' => 'keygen',
        'properties' => $properties,
        'publicKey' => $publicKey,
        'masterKey' => $masterKey,
        'userAttributes' => $userAttributes,
    ];
    $result = callApi($args);
    if (array_key_exists('privateKey', $result)) {
        return $result['privateKey'];
    }
    throw new EncryptionFailureException('Keygen Failed!', 1);
}

/**
 * @param string $publicKey
 * @param string $policy
 * @param string $inputFile
 * @param string $properties
 * @return string $encryptedFile: string
 * @throws EncryptionFailureException
 */
function encrypt(
    string $publicKey,
    string $policy,
    string $inputFile,
    string $properties = ENCRYPTION_PROPERTIES
): string {
    $args = [
        'method' => 'encrypt',
        'properties' => $properties,
        'publicKey' => $publicKey,
        'policy' => $policy,
        'inputFile' => curl_file_create($inputFile, 'application/octet-stream', 'inputFile')
    ];
    $result = callApi($args);
    if (array_key_exists('encryptedFile', $result)) {
        return base64_decode($result['encryptedFile']);
    }
    throw new EncryptionFailureException('Encrypt Failed!', 1);
}

/**
 * @param string $publicKey
 * @param string $privateKey
 * @param string $encryptedFile
 * @param string $properties
 * @return string $decryptedFile: string
 * @throws EncryptionFailureException
 */
function decrypt(
    string $publicKey,
    string $privateKey,
    string $encryptedFile,
    string $properties = ENCRYPTION_PROPERTIES
): string {
    $args = [
        'method' => 'decrypt',
        'properties' => $properties,
        'publicKey' => $publicKey,
        'privateKey' => $privateKey,
        'encryptedFile' => curl_file_create($encryptedFile, 'application/octet-stream', 'encryptedFile'),
    ];
    $result = callApi($args);
    if (array_key_exists('decryptedFile', $result)) {
        return base64_decode($result['decryptedFile']);
    }
    throw new EncryptionFailureException('Decrypt Failed!', 1);
}

