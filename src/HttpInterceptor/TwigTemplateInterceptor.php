<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpInterceptor;

use Distantmagic\Resonance\Attribute\Intercepts;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpInterceptor;
use Distantmagic\Resonance\SecurityPolicyHeaders;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;
use Twig\Environment as TwigEnvironment;

/**
 * @template-extends HttpInterceptor<TwigTemplate>
 */
#[Intercepts(TwigTemplate::class)]
#[Singleton(collection: SingletonCollection::HttpInterceptor)]
readonly class TwigTemplateInterceptor extends HttpInterceptor
{
    public function __construct(
        private SecurityPolicyHeaders $securityPolicyHeaders,
        private TwigEnvironment $twig,
    ) {}

    public function intercept(
        ServerRequestInterface $request,
        Response $response,
        object $intercepted,
    ): null {
        $rendered = $this->twig->render(
            $intercepted->getTemplatePath(),
            $intercepted->getTemplateData($request, $response),
        );

        $this->securityPolicyHeaders->sendTemplatedPagePolicyHeaders($request, $response);

        $response->header('content-type', ContentType::TextHtml->value.';charset=utf-8');
        $response->end($rendered);

        return null;
    }
}
