<?php

declare(strict_types=1);

namespace Resonance;

enum HttpControllerParameterResolutionStatus
{
    case Forbidden;
    case NotFound;
    case NotProvided;
    case Ok;
}
