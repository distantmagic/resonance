<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpMiddleware;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\HandlesMiddlewareAttribute;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\ValidatesCSRFToken;
use Distantmagic\Resonance\CSRFManager;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpMiddleware;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpMiddleware<ValidatesCSRFToken>
 */
#[HandlesMiddlewareAttribute(
    attribute: ValidatesCSRFToken::class,
    priority: 1100,
)]
#[Singleton(collection: SingletonCollection::HttpMiddleware)]
readonly class ValidatesCSRFTokenMiddleware extends HttpMiddleware
{
    public function __construct(
        private BadRequest $badRequest,
        private CSRFManager $csrfManager,
    ) {}

    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): HttpInterceptableInterface|HttpResponderInterface {
        $requestData = $request->{$attribute->requestDataSource->value};

        if (!is_array($requestData) || !$this->csrfManager->checkToken($request, $requestData)) {
            return $this->badRequest;
        }

        return $next;
    }
}
