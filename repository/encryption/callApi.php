<?php

declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';

/* Modified: https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php */
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
    return (array)json_decode((string)curl_exec($ch), true);
}

/* Return properties used for encryption*/
function generateProperties($type = 'a', $debug = false) {
    $args = (object)[
        'method' => 'generateProperties',
        'type' => $type
    ];

    return callApi($args, $debug);
}

/* Returns: $publicKey:string, $masterKey: string */
function setup($debug = false) {
    $args = (object)[
        'method' => 'setup',
        'properties' => ENCRYPTION_PROPERTIES,
    ];

    return callApi($args, $debug);
}

/*Return: privateKey: string */
function keygen($publicKey, $masterKey, $userAttributes, $debug = false) {
    $args = (object)[
        'method' => 'keygen',
        'properties' => ENCRYPTION_PROPERTIES,
        'publicKey' => $publicKey,
        'masterKey' => $masterKey,
        'userAttributes' => $userAttributes,
    ];

    return callApi($args, $debug);
}

/*Return: encryptedFile: string */
function encrypt($publicKey, $policy, $inputFile, $debug = false) {
    $args = (object)[
        'method' => 'keygen',
        'properties' => ENCRYPTION_PROPERTIES,
        'publicKey' => $publicKey,
        'policy' => $policy,
        'inputFile' => base64_encode($inputFile),
    ];

    return callApi($args, $debug);
}

/*Return: decryptedFile: string */
function decrypt($publicKey, $privateKey, $encryptedFile, $debug = false) {
    $args = (object)[
        'method' => 'keygen',
        'properties' => ENCRYPTION_PROPERTIES,
        'privateKey' => $privateKey,
        'encryptedFile' => base64_encode($encryptedFile),
    ];

    return callApi($args, $debug);
}
/*TODO: turn into test cases */
// $properties = generateProperties();
// var_dump($properties);
// $setupReturn = setup();
// $masterKey = $setupReturn['masterKey'];
// $publicKey = $setupReturn['publicKey'];
// // var_dump($setupReturn);
// $keygenReturn = keygen($publicKey, $masterKey, "userId:1");
// $privateKey = $keygenReturn['privateKey'];
// var_dump($keygenReturn);
