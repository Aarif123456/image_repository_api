<?php

declare(strict_types=1);
namespace ImageRepository\Model\FileManagement;

use ImageRepository\Exception\{InvalidAccessException, StaticClassAssertionError};
use ImageRepository\Model\Encryption\PolicyBuilder;
use ImageRepository\Model\User;

/**
 * Class selects policy builder depending on the access ID
 */
final class PolicySelector
{
    const PRIVATE_ACCESS = 1;
    const PUBLIC_ACCESS = 2;

    private function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @throws InvalidAccessException
     */
    public static function getPolicy(int $fileAccess, User $user): string {
        switch ($fileAccess) {
            case PolicySelector::PRIVATE_ACCESS:
                return PolicyBuilder::privatePolicy($user);
            case PolicySelector::PUBLIC_ACCESS:
                return PolicyBuilder::publicPolicy($user);
            default:
                throw new InvalidAccessException();
        }
    }

    public static function defaultAccess(): int {
        return PolicySelector::PRIVATE_ACCESS;
    }

    public static function isFilePublic(int $access): bool {
        return $access === PolicySelector::PUBLIC_ACCESS;
    }

    public static function isFilePrivate(int $access): bool {
        return $access === PolicySelector::PRIVATE_ACCESS;
    }

}

