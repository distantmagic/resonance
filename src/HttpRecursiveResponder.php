<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton]
readonly class HttpRecursiveResponder
{
    public function __construct(
        private HttpMiddlewareAggregate $httpMiddlewareAggregate,
    ) {}

    public function respondRecursive(
        Request $request,
        Response $response,
        null|HttpInterceptableInterface|HttpResponderInterface $responder,
    ): void {
        while ($responder) {
            $middlewareAttributes = $this->httpMiddlewareAggregate
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

                    if (!$responder) {
                        return;
                    }
                }
            } elseif ($responder instanceof HttpInterceptableInterface) {
                throw new LogicException(sprintf(
                    '%s has no middleware assigned',
                    $responder::class,
                ));
            }

            if ($responder instanceof HttpResponderInterface) {
                $responder = $responder->respond($request, $response);
            }
        }
    }
}
