<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum HttpControllerParameterResolutionStatus
{
    case Forbidden;
    case MissingUrlParameterValue;
    case NotFound;
    case Success;
    case ValidationErrors;
}
