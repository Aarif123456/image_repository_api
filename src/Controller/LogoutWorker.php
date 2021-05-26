<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

use ImageRepository\Views\JsonFormatter;

/**
 * Handles logic to logout user
 */
final class LogoutWorker extends AbstractWorker
{
    public function run() {
        JsonFormatter::printArray(['error' => !$this->auth->logout()]);
    }
}