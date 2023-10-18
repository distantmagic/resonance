<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton]
final readonly class HttpRecursiveResponder
{
    public function __construct(
        private HttpInterceptorAggregate $httpInterceptorAggregate,
        private HttpMiddlewareAggregate $httpMiddlewareAggregate,
    ) {}

    public function respondRecursive(
        Request $request,
        Response $response,
        null|HttpInterceptableInterface|HttpResponderInterface $responder,
    ): void {
        while ($responder) {
            $responder = $this->processMiddlewares($request, $response, $responder);

            if (!$responder) {
                return;
            }

            if ($responder instanceof HttpResponderInterface) {
                $forwardedResponder = $responder->respond($request, $response);

                if (!$forwardedResponder) {
                    return;
                }

                if ($forwardedResponder !== $responder) {
                    $this->respondRecursive($request, $response, $forwardedResponder);

                    return;
                }
            }

            if ($responder instanceof HttpInterceptableInterface) {
                $responder = $this->processInterceptors($request, $response, $responder);
            }
        }
    }

    private function processInterceptors(
        Request $request,
        Response $response,
        HttpInterceptableInterface $responder,
    ): null|HttpInterceptableInterface|HttpResponderInterface {
        $interceptor = $this
            ->httpInterceptorAggregate
            ->interceptors
            ->get($responder::class, null)
        ;

        if (!$interceptor) {
            return $responder;
        }

        $interceptorResponder = $interceptor->intercept($request, $response, $responder);

        if ($interceptorResponder !== $responder) {
            return $this->respondRecursive($request, $response, $interceptorResponder);
        }

        return $responder;
    }

    private function processMiddlewares(
        Request $request,
        Response $response,
        HttpInterceptableInterface|HttpResponderInterface $responder,
    ): null|HttpInterceptableInterface|HttpResponderInterface {
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

            if (!$middlewareResponder) {
                return null;
            }

            if ($middlewareResponder !== $responder) {
                return $this->respondRecursive($request, $response, $middlewareResponder);
            }
        }

        return $responder;
    }
}
