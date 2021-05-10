<?php

declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';

/**
 * Modified: https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
 * @param $args
 * @param false $debug
 * @param array $options
 * @return array
 * @throws Exception
 */
function callApi($args, $debug = false, $options = []): array {
    //transform the data for the POST request
    $fields = http_build_query($args);
    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    $defaults = [
        CURLOPT_URL => ENCRYPTION_ENDPOINT,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $fields,
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        CURLOPT_RETURNTRANSFER => true
    ];
    curl_setopt_array($ch, ($options + $defaults));

    // Make the the post request
    $result = (array)json_decode((string)curl_exec($ch), true);

    if (array_key_exists('Error', $result)) {
        throw new Exception($result['Error'], 1);
    }
    if (array_key_exists('error', $result)) {
        throw new Exception($result['error'], 1);
    }

    return $result;
}

/* Return properties used for encryption*/
function generateProperties($type = 'a', $debug = false): array {
    $args = (object)[
        'method' => 'generateProperties',
        'type' => $type
    ];

    return callApi($args, $debug);
}

/* Returns: $publicKey:string, $masterKey: string */
function setup($debug = false): array {
    $args = (object)[
        'method' => 'setup',
        'properties' => ENCRYPTION_PROPERTIES,
    ];

    return callApi($args, $debug);
}

/*Return: privateKey: string on success otherwise we get an error  */
function keygen($publicKey, $masterKey, $userAttributes, $debug = false): string {
    $args = (object)[
        'method' => 'keygen',
        'properties' => ENCRYPTION_PROPERTIES,
        'publicKey' => $publicKey,
        'masterKey' => $masterKey,
        'userAttributes' => $userAttributes,
    ];
    $result = callApi($args, $debug);
    if (array_key_exists('privateKey', $result)) return $result['privateKey'];

    throw new Exception('Keygen Failed!',1);
}

/*Return: encryptedFile: string */
function encrypt($publicKey, $policy, $inputFile, $debug = false): string {
    $args = (object)[
        'method' => 'encrypt',
        'properties' => ENCRYPTION_PROPERTIES,
        'publicKey' => $publicKey,
        'policy' => $policy,
        'inputFile' => base64_encode($inputFile),
    ];
    $result = callApi($args, $debug);
    if (array_key_exists('encryptedFile', $result)) return base64_decode($result['encryptedFile']);

    throw new Exception('Encrypt Failed!',1);
}

/*Return: decryptedFile: string */
function decrypt($publicKey, $privateKey, $encryptedFile, $debug = false): string {
    $args = (object)[
        'method' => 'decrypt',
        'properties' => ENCRYPTION_PROPERTIES,
        'publicKey' => $publicKey,
        'privateKey' => $privateKey,
        'encryptedFile' => base64_encode($encryptedFile),
    ];
    $result = callApi($args, $debug);
    if (array_key_exists('decryptedFile', $result)) return base64_decode($result['decryptedFile']);

    throw new Exception('Decrypt Failed!',1);
}
//TODO: turn into test cases
//$properties = generateProperties();
//var_dump($properties);
//$setupReturn = setup();
//$masterKey = $setupReturn['masterKey'];
//$publicKey = $setupReturn['publicKey'];
//var_dump($setupReturn);
//echo 'privat Key****************************************************************';
//$privateKey  = keygen($publicKey, $masterKey, 'userId:1 public:true');
//// var_dump($privateKey);
//$policy = 'userId:1 public:true 2of2';
//echo 'input****************************************************************';
//echo '<br>';
//$inputFile = '10220220w20dsdassaeadaed2edwd2e2wewdsaxsasdcedf33rer3r33r33e2e2';
////echo $inputFile;
////echo 'encrypted****************************************************************';
////echo '<br>';
//$encryptedFile = encrypt($publicKey, $policy, $inputFile);
////echo 'decrypted****************************************************************';
//echo '<br>';
//$decryptedFile = decrypt($publicKey, $privateKey, $encryptedFile);
//echo $decryptedFile;
//assert(strcmp ($inputFile , $decryptedFile)===0);
//echo("DONE");
////var_dump($decryptedFile);
////var_dump($inputFile);
////var_dump($encryptedFile);