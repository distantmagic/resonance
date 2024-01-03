<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum Feature implements FeatureInterface
{
    case OAuth2;

    public function getName(): string
    {
        return $this->name;
    }
}
