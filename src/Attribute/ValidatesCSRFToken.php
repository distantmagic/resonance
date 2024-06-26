<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\RequestDataSource;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION)]
final readonly class ValidatesCSRFToken extends BaseAttribute
{
    public function __construct(
        public string $name,
        public RequestDataSource $requestDataSource = RequestDataSource::Post,
    ) {}
}
