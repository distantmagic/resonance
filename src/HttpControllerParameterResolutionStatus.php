<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum HttpControllerParameterResolutionStatus
{
    case Forbidden;
    case NotFound;
    case NotProvided;
}
