<?php

declare(strict_types=1);

use App\Infrastructure\Container;
use App\Infrastructure\Http\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

new Container(dirname(__DIR__))
    ->kernel()
    ->handle(Request::fromGlobals())
    ->send();
