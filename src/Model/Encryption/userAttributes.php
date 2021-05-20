<?php

declare(strict_types=1);
namespace App\Model\Encryption;

/* Create user attributes for the encryption */
use App\Model\User;

function createUserAttributes(User $user): string {
    /* Set user id and give public access*/
    return "userId:$user->id public:true";
}
