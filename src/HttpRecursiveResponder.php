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
        private HttpPreprocessorAggregate $httpPreprocessorAggregate,
    ) {}

    public function respondRecursive(
        Request $request,
        Response $response,
        null|HttpInterceptableInterface|HttpResponderInterface $responder,
    ): void {
        while ($responder) {
            $preprocessorAttributes = $this->httpPreprocessorAggregate
                ->preprocessors
                ->get($responder::class, null)
            ;

            if ($preprocessorAttributes) {
                foreach ($preprocessorAttributes as $preprocessorAttribute) {
                    $responder = $preprocessorAttribute->httpPreprocessor->preprocess(
                        $request,
                        $response,
                        $preprocessorAttribute->attribute,
                        $responder,
                    );

                    if (!$responder) {
                        return;
                    }
                }
            } elseif ($responder instanceof HttpInterceptableInterface) {
                throw new LogicException(sprintf(
                    '%s has no preprocessor assigned',
                    $responder::class,
                ));
            }

            if ($responder instanceof HttpResponderInterface) {
                $responder = $responder->respond($request, $response);
            }
        }
    }
}
