<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

#[Singleton]
readonly class PsrSwooleResponder
{
    public function __construct(
        private CookieManager $cookieManager,
    ) {}

    public function respondWithPsrResponse(
        ServerRequestInterface $request,
        Response $response,
        ResponseInterface $psrResponse,
    ): void {
        /**
         * @var null|string $sendfile
         */
        $sendfile = null;

        foreach ($psrResponse->getHeaders() as $name => $values) {
            $headerLine = implode(', ', $values);

            if ('x-sendfile' !== $name) {
                $response->header((string) $name, $headerLine);
            } else {
                $sendfile = $headerLine;
            }
        }

        foreach ($this->cookieManager->getCookieJar($request) as $cookie) {
            $response->cookie(
                name: $cookie->getName(),
                value: $cookie->getValue() ?? '',
                expires: $cookie->getExpiresTime(),
                path: $cookie->getPath(),
                domain: $cookie->getDomain() ?? '',
                secure: $cookie->isSecure(),
                httponly: $cookie->isHttpOnly(),
                samesite: $cookie->getSameSite() ?? '',
            );
        }

        if (is_string($sendfile)) {
            if (!$response->sendfile($sendfile)) {
                $response->status(500, 'Unable to send file');
            }

            return;
        }

        $response->status(
            $psrResponse->getStatusCode(),
            $psrResponse->getReasonPhrase(),
        );
        $response->end($psrResponse->getBody()->getContents());
    }
}
