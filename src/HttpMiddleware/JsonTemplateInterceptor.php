<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpMiddleware;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\InterceptableJsonTemplate;
use Distantmagic\Resonance\Attribute\PreprocessesHttpResponder;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpMiddleware;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\JsonTemplate;
use Distantmagic\Resonance\SingletonCollection;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpMiddleware<InterceptableJsonTemplate>
 */
#[PreprocessesHttpResponder(
    attribute: InterceptableJsonTemplate::class,
    priority: 0,
)]
#[Singleton(collection: SingletonCollection::HttpMiddleware)]
readonly class JsonTemplateInterceptor extends HttpMiddleware
{
    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): null {
        if (!($next instanceof JsonTemplate)) {
            throw new LogicException('Expected '.JsonTemplate::class);
        }

        $response->header('content-type', ContentType::ApplicationJson->value);
        $response->end(json_encode(
            value: $next->data,
            flags: JSON_THROW_ON_ERROR,
        ));

        return null;
    }
}
