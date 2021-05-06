<?php

declare(strict_types=1);

require_once __DIR__ . '/../../common/constants.php';

function getPolicy($fileAccess, $user): string {
    switch ($fileAccess) {
        case PRIVATE_ACCESS:
            return "userID:$user->ID AND public";
        case PUBLIC_ACCESS:
            return "userID:$user->ID OR public";
        default:
            exit(INVALID_ACCESS_TYPE);
    }
}
