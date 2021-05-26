<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\Controller\ImageWorker;

use const ImageRepository\Utils\AUTHORIZED_USER;

$worker = new ImageWorker();
$worker->safeRun(AUTHORIZED_USER);
