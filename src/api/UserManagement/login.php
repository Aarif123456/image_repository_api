<?php

declare(strict_types=1);
namespace ImageRepository\api\UserManagement;

use ImageRepository\Views\ErrorHandler;

use const ImageRepository\Utils\UNAUTHENTICATED;

require_once __DIR__ . '/LoginWorker.php';
ErrorHandler::safeApiRun(UNAUTHENTICATED, 'LoginWorker::run');
