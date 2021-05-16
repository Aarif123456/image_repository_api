<?php
declare(strict_types=1);
function getPrivatePolicy(User $user): string {
    return "userId:$user->id public:true 2of2";
}

function getPublicPolicy(User $user): string {
    return "userId:$user->id public:true 1of2";
}