<?php 

declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';

/**
 * Modified: https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
 * @throws Exception
 */
function callApi($fields, array $options = [], bool $debug=false): array {
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
        throw new Exception($result['Error'], 1);
    }
    if (array_key_exists('error', $result)) {
        throw new Exception($result['error'], 1);
    }
    if($debug){
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
function setup(): array {
    $args = [
        'method' => 'setup',
        'properties' => ENCRYPTION_PROPERTIES,
    ];

    return callApi($args);
}

/*Return: privateKey: string on success otherwise we get an error  */
function keygen(string $publicKey, string $masterKey, string $userAttributes): string {
    $args = [
        'method' => 'keygen',
        'properties' => ENCRYPTION_PROPERTIES,
        'publicKey' => $publicKey,
        'masterKey' => $masterKey,
        'userAttributes' => $userAttributes,
    ];
    $result = callApi($args);
    if (array_key_exists('privateKey', $result)) return $result['privateKey'];

    throw new Exception('Keygen Failed!', 1);
}

/*Return: encryptedFile: string */
function encrypt(string $publicKey, string $policy, string $inputFile): string {
    $args = [
        'method' => 'encrypt',
        'properties' => ENCRYPTION_PROPERTIES,
        'publicKey' => $publicKey,
        'policy' => $policy,
        'inputFile' => curl_file_create($inputFile,'application/octet-stream','inputFile')
    ];  

    $result = callApi($args);
    if (array_key_exists('encryptedFile', $result)) return base64_decode($result['encryptedFile']);

    throw new Exception('Encrypt Failed!', 1);
}

/*Return: decryptedFile: string */
function decrypt(string $publicKey, string $privateKey, string $encryptedFile): string {
    $args = [
        'method' => 'decrypt',
        'properties' => ENCRYPTION_PROPERTIES,
        'publicKey' => $publicKey,
        'privateKey' => $privateKey,
        'encryptedFile' => curl_file_create($encryptedFile,'application/octet-stream','encryptedFile'),
    ];
    $result = callApi($args);
    if (array_key_exists('decryptedFile', $result)) return base64_decode($result['decryptedFile']);

    throw new Exception('Decrypt Failed!', 1);
}

//TODO: turn into test cases
// $properties = generateProperties();
// var_dump($properties);
// $setupReturn = setup();
// var_dump($setupReturn);
// $masterKey = $setupReturn['masterKey'] ?? '';
// $publicKey = $setupReturn['publicKey'] ?? '';
// echo 'private Key****************************************************************';
// $privateKey  = keygen($publicKey, $masterKey, 'userId:1 public:true');
// $policy = 'userId:1 public:true 2of2';
// echo 'input****************************************************************';
// echo '<br>';
// $inputFile = 'test.jpg';
// var_dump(curl_file_create($inputFile, 'application/octet-stream','inputFile'));
// //echo $inputFile;
// //echo 'encrypted****************************************************************';
// //echo '<br>';
// $encryptedFileBytes = encrypt($publicKey, $policy, $inputFile);
// $encryptedFile = $inputFile . '.ENCRYPTED';
// file_put_contents($encryptedFile, $encryptedFileBytes);
// //echo 'decrypted****************************************************************';
// echo '<br>';
// $decryptedFile = decrypt($publicKey, $privateKey, $encryptedFile);
// echo $decryptedFile;

// assert(strcmp ($inputFile , $decryptedFile)===0);
// echo("DONE");
//var_dump($decryptedFile);
//var_dump($inputFile);
//var_dump($encryptedFile);