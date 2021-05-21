<?php

declare(strict_types=1);
namespace ImageRepository\Api\UserManagement;

use ImageRepository\Views\ErrorHandler;

use const ImageRepository\Utils\UNAUTHENTICATED;

require_once __DIR__ . '/RegisterWorker.php';
ErrorHandler::safeApiRun(UNAUTHENTICATED, 'RegisterWorker::run');
