<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpMiddleware;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\HandlesMiddlewareAttribute;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\ValidatesCSRFToken;
use Distantmagic\Resonance\CSRFManager;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpMiddleware;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestDataSource;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template-extends HttpMiddleware<ValidatesCSRFToken>
 */
#[GrantsFeature(Feature::HttpSession)]
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
        ServerRequestInterface $request,
        ResponseInterface $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface {
        $requestData = match ($attribute->requestDataSource) {
            RequestDataSource::Get => $request->getQueryParams(),
            RequestDataSource::Post => $request->getParsedBody(),
        };

        if (!is_array($requestData) || !$this->csrfManager->checkToken($request, $requestData)) {
            return $this->badRequest;
        }

        return $next;
    }
}
