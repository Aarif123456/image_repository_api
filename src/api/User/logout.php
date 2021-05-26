<?php

declare(strict_types=1);
namespace ImageRepository\api\User;

use ImageRepository\Controller\LogoutWorker;

use const ImageRepository\Utils\AUTHORIZED_USER;

$worker = new LogoutWorker();
$worker->safeRun(AUTHORIZED_USER);