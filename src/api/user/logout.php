<?php

declare(strict_types=1);
namespace ImageRepository\api\User;

use ImageRepository\Views\ErrorHandler;

use const ImageRepository\Utils\AUTHORIZED_USER;

require_once __DIR__ . '/LogoutWorker.php';
ErrorHandler::safeApiRun(AUTHORIZED_USER, 'LogoutWorker::run');
