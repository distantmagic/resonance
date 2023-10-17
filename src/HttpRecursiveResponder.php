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
            $middlewareAttributes = $this
                ->httpMiddlewareAggregate
                ->middlewares
                ->get($responder::class, null)
            ;

            if ($middlewareAttributes) {
                foreach ($middlewareAttributes as $middlewareAttribute) {
                    $responder = $middlewareAttribute->httpMiddleware->preprocess(
                        $request,
                        $response,
                        $middlewareAttribute->attribute,
                        $responder,
                    );

                    if ($responder) {
                        $responder = $this->doIntercept($request, $response, $responder);
                    }

                    if (!$responder) {
                        return;
                    }
                }
            }

            if ($responder instanceof HttpResponderInterface) {
                $responder = $responder->respond($request, $response);
            }

            $responder = $this->doIntercept($request, $response, $responder);
        }
    }

    private function doIntercept(
        Request $request,
        Response $response,
        null|HttpInterceptableInterface|HttpResponderInterface $responder,
    ): null|HttpInterceptableInterface|HttpResponderInterface {
        if (!$responder instanceof HttpInterceptableInterface) {
            return $responder;
        }

        $interceptor = $this
            ->httpInterceptorAggregate
            ->interceptors
            ->get($responder::class, null)
        ;

        if ($interceptor) {
            return $interceptor->intercept($request, $response, $responder);
        }

        return $responder;
    }
}
