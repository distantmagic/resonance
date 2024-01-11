<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum OpenAPIParameterIn: string
{
    case Cookie = 'cookie';
    case Header = 'header';
    case Path = 'path';
    case Query = 'query';
}
