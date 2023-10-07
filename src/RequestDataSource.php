<?php

declare(strict_types=1);

namespace Resonance;

enum RequestDataSource: string
{
    case Get = 'get';
    case Post = 'post';
}
