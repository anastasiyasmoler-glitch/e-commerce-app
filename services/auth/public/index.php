<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Auth\Health;

header('Content-Type: application/json');

echo json_encode((new Health())->payload(), JSON_THROW_ON_ERROR);
