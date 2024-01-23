<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\ServerPipeMessageInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class HandlesServerPipeMessage extends BaseAttribute
{
    /**
     * @param class-string<ServerPipeMessageInterface> $className
     */
    public function __construct(
        public string $className,
    ) {}
}
