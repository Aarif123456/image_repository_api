<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

require_once __DIR__ . '/../../../vendor/autoload.php';
use ImageRepository\Controller\ImageWorker;

use const ImageRepository\Utils\AUTHORIZED_USER;

$worker = new ImageWorker();
$worker->safeRun(AUTHORIZED_USER);
