<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ServerRequestInterface;
use WeakMap;

#[Singleton]
readonly class ContentSecurityPolicyRulesRepository
{
    /**
     * @var WeakMap<ServerRequestInterface,ContentSecurityPolicyRequestRules>
     */
    private WeakMap $rules;

    public function __construct()
    {
        /**
         * @var WeakMap<ServerRequestInterface,ContentSecurityPolicyRequestRules>
         */
        $this->rules = new WeakMap();
    }

    public function from(ServerRequestInterface $request): ContentSecurityPolicyRequestRules
    {
        $rules = $this->get($request);

        if ($rules) {
            return $rules;
        }

        $rules = new ContentSecurityPolicyRequestRules();
        $this->rules->offsetSet($request, $rules);

        return $rules;
    }

    public function get(ServerRequestInterface $request): ?ContentSecurityPolicyRequestRules
    {
        if (!$this->has($request)) {
            return null;
        }

        return $this->rules->offsetGet($request);
    }

    public function has(ServerRequestInterface $request): bool
    {
        return $this->rules->offsetExists($request);
    }
}
