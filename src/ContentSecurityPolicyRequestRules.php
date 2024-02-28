<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class ContentSecurityPolicyRequestRules
{
    public ContentSecurityPolicyDirectives $fontSrc;
    public ContentSecurityPolicyDirectives $formAction;
    public ContentSecurityPolicyDirectives $frameSrc;
    public ContentSecurityPolicyDirectives $scriptSrc;
    public ContentSecurityPolicyDirectives $styleSrc;

    public function __construct()
    {
        $this->fontSrc = new ContentSecurityPolicyDirectives(["'self'"]);
        $this->formAction = new ContentSecurityPolicyDirectives(["'self'"]);
        $this->frameSrc = new ContentSecurityPolicyDirectives([], ["'none'"]);
        $this->scriptSrc = new ContentSecurityPolicyDirectives(["'self'"]);
        $this->styleSrc = new ContentSecurityPolicyDirectives(["'self'"]);
    }
}
