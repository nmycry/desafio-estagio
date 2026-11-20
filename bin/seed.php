<?php

declare(strict_types=1);

use App\Infrastructure\Container;

require dirname(__DIR__) . '/vendor/autoload.php';

['created' => $created, 'skipped' => $skipped] = new Container(dirname(__DIR__))->citizenSeeder()->run();

printf("%d cidadãos cadastrados, %d já existiam.\n", $created, $skipped);
