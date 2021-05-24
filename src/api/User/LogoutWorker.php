<?php

declare(strict_types=1);
namespace ImageRepository\api\User;

use ImageRepository\Exception\StaticClassAssertionError;
use ImageRepository\Model\Database;
use ImageRepository\Utils\Auth;
use ImageRepository\Views\JsonFormatter;

/**
 * Handles logic to logout user
 */
final class LogoutWorker
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function run(Database $_db, Auth $auth, bool $_debug) {
        JsonFormatter::printArray(['error' => !$auth->logout()]);
    }
}