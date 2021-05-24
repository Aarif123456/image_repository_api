<?php

declare(strict_types=1);
namespace ImageRepository\Utils;

const DEBUG = true;
const UNAUTHENTICATED = 1;
const AUTHORIZED_USER = 2;
const AUTHORIZED_ADMIN = 3;
/* Around 7 MB - limitation comes from GCP's limit of 10 MB per request */
const MAX_FILE_SIZE = 7340063;

