<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Each time the responder swaps to a adifferent one, start processing from
 * the beginning / middleware.
 */
#[Singleton]
final readonly class HttpRecursiveResponder
{
    public function __construct(
        private HttpInterceptorAggregate $httpInterceptorAggregate,
        private HttpMiddlewareAggregate $httpMiddlewareAggregate,
    ) {}

    public function respondRecursive(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpInterceptableInterface|HttpResponderInterface|ResponseInterface $responder,
    ): ResponseInterface {
        while (!($responder instanceof ResponseInterface)) {
            $responder = $this->processMiddlewares($request, $response, $responder);

            if ($responder instanceof HttpResponderInterface) {
                $forwardedResponder = $responder->respond($request, $response);

                if ($forwardedResponder !== $responder) {
                    return $this->respondRecursive($request, $response, $forwardedResponder);
                }
            }

            if ($responder instanceof HttpInterceptableInterface) {
                $responder = $this->processInterceptors($request, $response, $responder);
            }
        }

        return $responder;
    }

    private function processInterceptors(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpInterceptableInterface $responder,
    ): HttpInterceptableInterface|ResponseInterface {
        $interceptor = $this
            ->httpInterceptorAggregate
            ->interceptors
            ->get($responder::class, null)
        ;

        if (!$interceptor) {
            throw new LogicException('There is no interceptor registered to handle: '.$responder::class);
        }

        $interceptorResponder = $interceptor->intercept($request, $response, $responder);

        if ($interceptorResponder !== $responder) {
            return $this->respondRecursive($request, $response, $interceptorResponder);
        }

        return $responder;
    }

    private function processMiddlewares(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpInterceptableInterface|HttpResponderInterface $responder,
    ): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface {
        $middlewareAttributes = $this
            ->httpMiddlewareAggregate
            ->middlewares
            ->get($responder::class, null)
        ;

        if (!$middlewareAttributes) {
            return $responder;
        }

        foreach ($middlewareAttributes as $middlewareAttribute) {
            $middlewareResponder = $middlewareAttribute->httpMiddleware->preprocess(
                $request,
                $response,
                $middlewareAttribute->attribute,
                $responder,
            );

            if ($middlewareResponder !== $responder) {
                return $this->respondRecursive($request, $response, $middlewareResponder);
            }
        }

        return $responder;
    }
}
