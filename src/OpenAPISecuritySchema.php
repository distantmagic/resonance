<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum OpenAPISecuritySchema
{
    case OAuth2;
    case Session;
}
