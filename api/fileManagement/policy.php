<?php

declare(strict_types=1);

require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../views/apiReturn.php';

function getPolicy($fileAccess, $user): string {
    switch ($fileAccess) {
        case PRIVATE_ACCESS:
            return "public:true 1of1";
        case PUBLIC_ACCESS:
            $userID = $user->id;

            return "userID:$userID 1of1";
        default:
            exitWithError(INVALID_ACCESS_TYPE);
    }
}
