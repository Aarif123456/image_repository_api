<?php

declare(strict_types=1);
namespace ImageRepository\api\UserManagement;

use ImageRepository\Controller\RegisterWorker;

use const ImageRepository\Utils\UNAUTHENTICATED;

$worker = new RegisterWorker();
$worker->safeRun(UNAUTHENTICATED);
