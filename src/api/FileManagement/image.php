<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\Views\ErrorHandler;

use const ImageRepository\Utils\AUTHORIZED_USER;

ErrorHandler::safeApiRun(AUTHORIZED_USER, 'ImageWorker::run');

