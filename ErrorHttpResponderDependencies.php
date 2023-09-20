<?php

declare(strict_types=1);

namespace Resonance;

use App\Template\Layout\Json\Error as JsonErrorTemplate;
use App\Template\Layout\Turbo\Error as HtmlErrorTemplate;
use Resonance\Attribute\Singleton;
use Resonance\HttpResponder\NotAcceptable;

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
