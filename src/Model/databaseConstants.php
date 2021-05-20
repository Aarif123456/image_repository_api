<?php

declare(strict_types=1);
namespace App\Model;

const PRIVATE_ACCESS = 1;
const PUBLIC_ACCESS = 2;
function isFilePublic(int $access): bool {
    return $access === PUBLIC_ACCESS;
}

function isFilePrivate(int $access): bool {
    return $access === PRIVATE_ACCESS;
}
