<?php

declare(strict_types=1);
namespace ImageRepository\Model\Encryption;

use ImageRepository\Exception\StaticClassAssertionError;
use ImageRepository\Model\User;

/**
 * Builds the policy used during encryption
 */
final class PolicyBuilder
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function privatePolicy(User $user): string {
        return "userId:$user->id public:true 2of2";
    }

    public static function publicPolicy(User $user): string {
        return "userId:$user->id public:true 1of2";
    }
}