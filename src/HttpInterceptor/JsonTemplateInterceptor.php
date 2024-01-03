<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpInterceptor;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\Intercepts;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\HttpInterceptor;
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
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
    ) {}

    public function intercept(
        Request $request,
        Response $response,
        object $intercepted,
    ): null {
        $response->header('content-type', ContentType::ApplicationJson->value);
        $response->end(json_encode(
            value: $intercepted->data,
            flags: Environment::Production === $this->applicationConfiguration->environment
                ? JSON_THROW_ON_ERROR
                : JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
        ));

        return null;
    }
}
