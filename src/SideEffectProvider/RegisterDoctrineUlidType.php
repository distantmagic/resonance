<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SideEffectProvider;

use Distantmagic\Resonance\Attribute\SideEffect;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\SideEffectProvider;
use Doctrine\DBAL\Types\Type;
use RuntimeException;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[SideEffect(Feature::Doctrine)]
#[Singleton]
readonly class RegisterDoctrineUlidType extends SideEffectProvider
{
    public function provideSideEffect(): void
    {
        if (!Type::hasType('ulid')) {
            Type::addType('ulid', UlidType::class);
        } elseif (!(Type::getType('ulid') instanceof UlidType)) {
            throw new RuntimeException(sprintf(
                'Expected Doctrine to use %s as ulid type',
                UlidType::class,
            ));
        }
    }
}
