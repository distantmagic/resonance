<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class DoctrineEntityManager extends BaseAttribute
{
    public function __construct(public string $connection = 'default') {}
}
