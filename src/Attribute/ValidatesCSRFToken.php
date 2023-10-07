<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use Attribute;
use Resonance\Attribute as BaseAttribute;
use Resonance\RequestDataSource;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ValidatesCSRFToken extends BaseAttribute
{
    public function __construct(
        public RequestDataSource $requestDataSource = RequestDataSource::Post,
    ) {}
}
