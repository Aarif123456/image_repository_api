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
    switch ($fileAccess ?? PRIVATE_ACCESS) {
        case PRIVATE_ACCESS:
            $policy = 'public:true 1of1';
            break;
        case PUBLIC_ACCESS:
            $userID = $user->id;
            $policy = "userID:$userID 1of1";
            break;
        default:
            throw new Exception(INVALID_ACCESS_TYPE);
    }

    return $policy;
}
