<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpInterceptor;

use Distantmagic\Resonance\Attribute\Intercepts;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptor;
use Distantmagic\Resonance\HttpResponder\Json;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\JsonTemplate;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template-extends HttpInterceptor<JsonTemplate>
 */
#[Intercepts(JsonTemplate::class)]
#[Singleton(collection: SingletonCollection::HttpInterceptor)]
readonly class JsonTemplateInterceptor extends HttpInterceptor
{
    public function __construct(private JsonSerializer $jsonSerializer) {}

    public function intercept(
        ServerRequestInterface $request,
        ResponseInterface $response,
        object $intercepted,
    ): HttpResponderInterface {
        return new Json($this->jsonSerializer->serialize($intercepted->data));
    }
}
