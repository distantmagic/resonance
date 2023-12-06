<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use GuzzleHttp\Promise\PromiseInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Coroutine\Http\Client;

use function Swoole\Coroutine\Http\request;

readonly class SwooleHttpHandler
{
    public function __invoke(RequestInterface $request): PromiseInterface
    {
        $promise = GuzzlePromiseAdapter::fromExecutor($this->fetchResponse(...));
        $promise->resolve($request);

        return $promise;
    }

    private function fetchResponse(RequestInterface $request): ?ResponseInterface
    {
        parse_str((string) $request->getBody(), $body);

        /**
         * @var Client
         */
        $swooleResponse = request(
            (string) $request->getUri(),
            $request->getMethod(),
            $body,
            [
                'timeout' => 1,
            ],
            $request->getHeaders(),
        );

        /**
         * @var null|string
         */
        $swooleResponseBody = $swooleResponse->getBody() ?? null;

        /**
         * @var array
         */
        $swooleResponseHeaders = $swooleResponse->getHeaders() ?? [];

        /**
         * @var false|int
         */
        $swooleResponseStatusCode = $swooleResponse->getStatusCode();

        if (!$swooleResponseStatusCode) {
            return null;
        }

        return new Response(
            $swooleResponseStatusCode,
            $swooleResponseHeaders,
            $swooleResponseBody,
        );
    }
}
