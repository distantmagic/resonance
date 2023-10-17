<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpPreprocessor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\InterceptableTwigTemplate;
use Distantmagic\Resonance\Attribute\PreprocessesHttpResponder;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpPreprocessor;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Twig\Environment as TwigEnvironment;

/**
 * @template-extends HttpPreprocessor<InterceptableTwigTemplate>
 */
#[PreprocessesHttpResponder(
    attribute: InterceptableTwigTemplate::class,
    priority: 0,
)]
#[Singleton(collection: SingletonCollection::HttpPreprocessor)]
readonly class TwigTemplateInterceptor extends HttpPreprocessor
{
    public function __construct(private TwigEnvironment $twig) {}

    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): null {
        if (!($next instanceof TwigTemplate)) {
            throw new LogicException('Expected '.TwigTemplate::class);
        }

        $rendered = $this->twig->render(
            $next->getTemplatePath(),
            $next->getTemplateData($request, $response),
        );

        $response->header('content-type', ContentType::TextHtml->value.';charset=utf-8');
        $response->end($rendered);

        return null;
    }
}
