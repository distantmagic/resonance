<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Resonance\HttpResponder\NotAcceptable;

#[Singleton]
readonly class ErrorHttpResponderDependencies
{
    public function __construct(
        public HtmlErrorTemplateInterface $htmlTemplate,
        public JsonErrorTemplateInterface $jsonTemplate,
        public NotAcceptable $notAcceptable,
        public SecurityPolicyHeaders $securityPolicyHeaders,
    ) {}
}
