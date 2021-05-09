<?php

declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';

/**
 * @param $fileAccess
 * @param $user
 * @return string
 * @throws Exception
 */
function getPolicy($fileAccess, $user): string {
    $userID = $user->id;
    switch ($fileAccess ?? PRIVATE_ACCESS) {
        case PRIVATE_ACCESS:
            $policy = "userId:$userID public:true 2of2";
            break;
        case PUBLIC_ACCESS:
            $policy = "userId:$userID public:true 1of2";
            break;
        default:
            throw new Exception(INVALID_ACCESS_TYPE);
    }

    return $policy;
}
