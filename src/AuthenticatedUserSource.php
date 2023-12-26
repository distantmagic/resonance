<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum AuthenticatedUserSource
{
    case OAuth2;
    case Session;
}
