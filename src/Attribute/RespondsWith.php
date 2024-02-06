<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\ContentType;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class RespondsWith extends BaseAttribute
{
    /**
     * @param null|non-empty-string $description
     */
    public function __construct(
        public ContentType $contentType,
        public Constraint $constraint,
        public int $status,
        public ?string $description = null,
    ) {}
}
