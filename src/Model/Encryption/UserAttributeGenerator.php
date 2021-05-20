<?php

declare(strict_types=1);
namespace ImageRepository\Model\Encryption;

use ImageRepository\Exception\StaticClassAssertionError;
use ImageRepository\Model\User;

/**
 * Create user attributes for the encryption
 */
final class UserAttributeGenerator
{
    function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function generate(User $user): string {
        /* Set user id and give public access*/
        return "userId:$user->id public:true";
    }
}