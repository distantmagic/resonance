<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum Feature implements FeatureInterface
{
    case OAuth2;
    case OpenAPI;
    case WebSocket;

    public function getName(): string
    {
        return $this->name;
    }
}
