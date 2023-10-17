<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\ContentSecurityPolicy;
use JsonSerializable;
use Stringable;

#[ContentSecurityPolicy(ContentSecurityPolicyType::Json)]
final readonly class JsonTemplate implements HttpInterceptableInterface
{
    public function __construct(public array|JsonSerializable|string|Stringable $data) {}
}
