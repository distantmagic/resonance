<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use WeakMap;

#[Singleton]
readonly class ContentSecurityPolicyRulesRepository
{
    /**
     * @var WeakMap<Request,ContentSecurityPolicyRequestRules>
     */
    private WeakMap $rules;

    public function __construct()
    {
        /**
         * @var WeakMap<Request,ContentSecurityPolicyRequestRules>
         */
        $this->rules = new WeakMap();
    }

    public function from(Request $request): ContentSecurityPolicyRequestRules
    {
        $rules = $this->get($request);

        if ($rules) {
            return $rules;
        }

        $rules = new ContentSecurityPolicyRequestRules();
        $this->rules->offsetSet($request, $rules);

        return $rules;
    }

    public function get(Request $request): ?ContentSecurityPolicyRequestRules
    {
        if (!$this->has($request)) {
            return null;
        }

        return $this->rules->offsetGet($request);
    }

    public function has(Request $request): bool
    {
        return $this->rules->offsetExists($request);
    }
}
