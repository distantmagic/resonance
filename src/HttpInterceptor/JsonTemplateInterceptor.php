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
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpInterceptor<JsonTemplate>
 */
#[Intercepts(JsonTemplate::class)]
#[Singleton(collection: SingletonCollection::HttpInterceptor)]
readonly class JsonTemplateInterceptor extends HttpInterceptor
{
    public function __construct(private JsonSerializer $jsonSerializer) {}

    public function intercept(
        Request $request,
        Response $response,
        object $intercepted,
    ): HttpResponderInterface {
        return new Json($this->jsonSerializer->serialize($intercepted->data));
    }
}
