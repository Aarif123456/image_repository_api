<?php

declare(strict_types=1);
namespace ImageRepository\api\User;

require_once __DIR__ . '/../../../vendor/autoload.php';
use ImageRepository\Controller\LogoutWorker;

use const ImageRepository\Utils\AUTHORIZED_USER;

$worker = new LogoutWorker();
$worker->safeRun(AUTHORIZED_USER);