<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpPreprocessor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\ContentSecurityPolicy;
use Distantmagic\Resonance\Attribute\PreprocessesHttpResponder;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentSecurityPolicyType;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpPreprocessor;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SecurityPolicyHeaders;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpPreprocessor<ContentSecurityPolicy>
 */
#[PreprocessesHttpResponder(
    attribute: ContentSecurityPolicy::class,
    priority: 900,
)]
#[Singleton(collection: SingletonCollection::HttpPreprocessor)]
readonly class ContentSecurityPolicyPreprocessor extends HttpPreprocessor
{
    public function __construct(
        private SecurityPolicyHeaders $securityPolicyHeaders,
    ) {}

    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): HttpInterceptableInterface|HttpResponderInterface {
        match ($attribute->contentSecurityPolicyType) {
            ContentSecurityPolicyType::Html => $this->securityPolicyHeaders->sendTemplatedPagePolicyHeaders($request, $response),
            ContentSecurityPolicyType::Json => $this->securityPolicyHeaders->sendJsonPagePolicyHeaders($response),
        };

        return $next;
    }
}
