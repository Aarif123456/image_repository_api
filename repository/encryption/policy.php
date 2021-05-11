<?php

declare(strict_types=1);

require_once __DIR__ . '../databaseConstants.php';

function getPrivatePolicy(User $user) {
    return "userId:$user->id public:true 2of2";
}


function getPublicPolicy(User $user) {
    return "userId:$user->id public:true 1of2";
}