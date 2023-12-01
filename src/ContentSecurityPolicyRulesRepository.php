<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Map;
use Swoole\Http\Request;

#[Singleton]
readonly class ContentSecurityPolicyRulesRepository
{
    /**
     * @var Map<Request,ContentSecurityPolicyRequestRules>
     */
    private Map $rules;

    public function __construct()
    {
        $this->rules = new Map();
    }

    public function from(Request $request): ContentSecurityPolicyRequestRules
    {
        $rules = $this->get($request);

        if ($rules) {
            return $rules;
        }

        $rules = new ContentSecurityPolicyRequestRules();
        $this->rules->put($request, $rules);

        return $rules;
    }

    public function get(Request $request): ?ContentSecurityPolicyRequestRules
    {
        return $this->rules->get($request, null);
    }

    public function has(Request $request): bool
    {
        return $this->rules->hasKey($request);
    }
}
