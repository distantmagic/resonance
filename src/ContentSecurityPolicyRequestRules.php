<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class ContentSecurityPolicyRequestRules
{
    public ContentSecurityPolicyDirectives $formAction;

    public function __construct()
    {
        $this->formAction = new ContentSecurityPolicyDirectives(["'self'"]);
    }
}
