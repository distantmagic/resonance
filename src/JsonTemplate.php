<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\ContentSecurityPolicy;
use Distantmagic\Resonance\Attribute\InterceptableJsonTemplate;
use JsonSerializable;
use Stringable;

#[ContentSecurityPolicy(ContentSecurityPolicyType::Json)]
#[InterceptableJsonTemplate]
final readonly class JsonTemplate implements HttpInterceptableInterface
{
    public function __construct(public array|JsonSerializable|string|Stringable $data) {}
}
