<?php

declare(strict_types=1);

/* Create user attributes for the encryption */
function createUserAttributes(User $user): string {
    /* Set user id and give public access*/
    return "userId:$user->id public:true";
}
