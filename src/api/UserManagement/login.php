<?php

declare(strict_types=1);
namespace ImageRepository\api\UserManagement;

use ImageRepository\Controller\LoginWorker;

use const ImageRepository\Utils\UNAUTHENTICATED;

$worker = new LoginWorker();
$worker->safeRun(UNAUTHENTICATED);
