<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum RequestDataSource: string
{
    case Get = 'get';
    case Post = 'post';
}
