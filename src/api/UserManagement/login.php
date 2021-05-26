<?php

declare(strict_types=1);
namespace ImageRepository\api\UserManagement;

require_once __DIR__ . '/../../../vendor/autoload.php';
use ImageRepository\Controller\LoginWorker;

use const ImageRepository\Utils\UNAUTHENTICATED;

$worker = new LoginWorker();
$worker->safeRun(UNAUTHENTICATED);
