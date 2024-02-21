<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SideEffectProvider;

use Distantmagic\Resonance\Attribute\SideEffect;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\SideEffectProvider;
use Doctrine\DBAL\Types\Type;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[SideEffect(Feature::Doctrine)]
#[Singleton]
readonly class RegisterDoctrineUlidType extends SideEffectProvider
{
    public function provideSideEffect(): void
    {
        Type::addType('ulid', UlidType::class);
    }
}
