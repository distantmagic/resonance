<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpInterceptor;

use Distantmagic\Resonance\Attribute\Intercepts;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpInterceptor;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Twig\Environment as TwigEnvironment;

/**
 * @template-extends HttpInterceptor<TwigTemplate>
 */
#[Intercepts(TwigTemplate::class)]
#[Singleton(collection: SingletonCollection::HttpInterceptor)]
readonly class TwigTemplateInterceptor extends HttpInterceptor
{
    public function __construct(private TwigEnvironment $twig) {}

    public function intercept(
        Request $request,
        Response $response,
        object $intercepted,
    ): null {
        $rendered = $this->twig->render(
            $intercepted->getTemplatePath(),
            $intercepted->getTemplateData($request, $response),
        );

        $response->header('content-type', ContentType::TextHtml->value.';charset=utf-8');
        $response->end($rendered);

        return null;
    }
}
