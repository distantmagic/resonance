<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpInterceptor;

use Distantmagic\Resonance\Attribute\Intercepts;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpInterceptor;
use Distantmagic\Resonance\PsrStringStream;
use Distantmagic\Resonance\SecurityPolicyHeaders;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        ResponseInterface $response,
        object $intercepted,
    ): ResponseInterface {
        $rendered = $this->twig->render(
            $intercepted->getTemplatePath(),
            $intercepted->getTemplateData($request, $response),
        );

        return $this
            ->securityPolicyHeaders
            ->sendTemplatedPagePolicyHeaders($request, $response)
            ->withHeader('content-type', ContentType::TextHtml->value.';charset=utf-8')
            ->withBody(new PsrStringStream($rendered))
        ;
    }
}
