<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\Controller\UploadWorker;

use const ImageRepository\Utils\AUTHORIZED_USER;

$worker = new UploadWorker();
$worker->safeRun(AUTHORIZED_USER);
