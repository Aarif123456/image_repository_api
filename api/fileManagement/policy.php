<?php

declare(strict_types=1);

require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../views/apiReturn.php';

function getPolicy($fileAccess, $user): string {
    $policy = '';
    switch ($fileAccess) {
        case PRIVATE_ACCESS:
            $policy = 'public:true 1of1';
            break;
        case PUBLIC_ACCESS:
            $userID = $user->id;
            $policy = "userID:$userID 1of1";
            break;
        default:
            exitWithError(INVALID_ACCESS_TYPE);
    }

    return $policy;
}
