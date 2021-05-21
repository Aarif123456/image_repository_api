<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Exception\StaticClassAssertionError;
use ImageRepository\Model\Database;
use ImageRepository\Utils\Auth;
use ImageRepository\Views\JsonFormatter;

/**
 *
 */
final class LogoutWorker
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function run(Database $_db, Auth $auth, bool $debug) {
        JsonFormatter::printArray(['error' => !$auth->logout()]);
    }
}