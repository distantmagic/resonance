<?php

declare(strict_types=1);

namespace Resonance;

use App\Template\Layout\Turbo\Error as HtmlErrorTemplate;
use Resonance\Attribute\Singleton;
use Resonance\HttpResponder\NotAcceptable;
use Resonance\Template\Layout\Json\Error as JsonErrorTemplate;

#[Singleton]
readonly class ErrorHttpResponderDependencies
{
    public function __construct(
        public HtmlErrorTemplate $htmlTemplate,
        public JsonErrorTemplate $jsonTemplate,
        public NotAcceptable $notAcceptable,
        public SecurityPolicyHeaders $securityPolicyHeaders,
    ) {}
}
