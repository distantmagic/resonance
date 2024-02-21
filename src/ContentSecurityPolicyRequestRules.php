<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class ContentSecurityPolicyRequestRules
{
    public ContentSecurityPolicyDirectives $formAction;
    public ContentSecurityPolicyDirectives $frameSrc;
    public ContentSecurityPolicyDirectives $scriptSrc;

    public function __construct()
    {
        $this->formAction = new ContentSecurityPolicyDirectives(["'self'"]);
        $this->frameSrc = new ContentSecurityPolicyDirectives([], ["'none'"]);
        $this->scriptSrc = new ContentSecurityPolicyDirectives(["'self'"]);
    }
}
