<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpPreprocessor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\PreprocessesHttpResponder;
use Distantmagic\Resonance\Attribute\RenderableTwigTemplate;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpPreprocessor;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\Template\Layout\Twig as TwigTemplate;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Twig\Environment as TwigEnvironment;

/**
 * @template-extends HttpPreprocessor<RenderableTwigTemplate>
 */
#[PreprocessesHttpResponder(
    attribute: RenderableTwigTemplate::class,
    priority: 0,
)]
#[Singleton(collection: SingletonCollection::HttpPreprocessor)]
readonly class TwigTemplateRendererPreprocessor extends HttpPreprocessor
{
    public function __construct(private TwigEnvironment $twig) {}

    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpResponderInterface $next,
    ): null {
        if (!($next instanceof TwigTemplate)) {
            throw new LogicException(sprintf(
                'Only %s can have a %s attribute.',
                TwigTemplate::class,
                RenderableTwigTemplate::class,
            ));
        }

        $rendered = $this->twig->render(
            $next->getTemplatePath(),
            $next->getTemplateData($request, $response),
        );

        $next->sendContentTypeHeader($request, $response);
        $response->end($rendered);

        return null;
    }
}
