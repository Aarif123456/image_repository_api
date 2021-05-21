<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Views\ErrorHandler;

use const ImageRepository\Utils\AUTHORIZED_USER;

require_once __DIR__ . '/UploadWorker.php';
ErrorHandler::safeApiRun(AUTHORIZED_USER, 'UploadWorker::run');

