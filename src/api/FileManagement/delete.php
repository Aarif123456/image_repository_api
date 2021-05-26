<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

require_once __DIR__ . '/../../../vendor/autoload.php';
use ImageRepository\Controller\DeleteWorker;

use const ImageRepository\Utils\AUTHORIZED_USER;

$worker = new DeleteWorker();
$worker->safeRun(AUTHORIZED_USER);