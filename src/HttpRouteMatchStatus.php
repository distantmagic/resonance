<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum HttpRouteMatchStatus
{
    case Found;
    case MethodNotAllowed;
    case NotFound;
}
