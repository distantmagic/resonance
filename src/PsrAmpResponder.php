<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Amp\Http\Cookie\CookieAttributes;
use Amp\Http\Cookie\ResponseCookie;
use Amp\Http\Server\Response;
use DateTimeImmutable;
use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Cookie;

#[Singleton]
readonly class PsrAmpResponder
{
    public function __construct(
        private CookieManager $cookieManager,
    ) {}

    public function respondWithPsrResponse(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): Response {
        /**
         * @var array<non-empty-string,array<non-empty-string>> $headers
         */
        $headers = $response->getHeaders();

        $ampResponse = new Response(
            headers: $headers,
            body: $response->getBody()->getContents(),
        );

        $ampResponse->setStatus(
            $response->getStatusCode(),
            $response->getReasonPhrase(),
        );

        // /**
        //  * @var null|string $sendfile
        //  */
        // $sendfile = null;

        // foreach ($response->getHeaders() as $name => $values) {
        // $headerLine = implode(', ', $values);

        // if ('x-sendfile' !== $name) {
        // $response->header((string) $name, $headerLine);
        // } else {
        // $sendfile = $headerLine;
        // }
        // }

        foreach ($this->cookieManager->getCookieJar($request) as $cookie) {
            $cookieName = $cookie->getName();

            if (!empty($cookieName)) {
                $ampResponse->setCookie(new ResponseCookie(
                    name: $cookieName,
                    value: $cookie->getValue() ?? '',
                    attributes: $this->createCookieAttributes($cookie),
                ));
            }
        }

        // if (is_string($sendfile)) {
        //     if (!$response->sendfile($sendfile)) {
        //         $response->status(500, 'Unable to send file');
        //     }

        //     return;
        // }

        return $ampResponse;
    }

    private function createCookieAttributes(Cookie $cookie): CookieAttributes
    {
        $cookieAttributes = CookieAttributes::default();
        $expiresAt = (new DateTimeImmutable())->setTimestamp($cookie->getExpiresTime());

        $cookieAttributes = $cookieAttributes
            ->withDomain($cookie->getDomain() ?? '')
            ->withExpiry($expiresAt)
            ->withPath($cookie->getPath())
        ;

        $cookieAttributes = $cookie->isSecure()
            ? $cookieAttributes->withSecure()
            : $cookieAttributes->withoutSecure();

        $cookieAttributes = $cookie->isHttpOnly()
            ? $cookieAttributes->withHttpOnly()
            : $cookieAttributes->withoutHttpOnly();

        $sameSite = $cookie->getSameSite();

        return $sameSite
            ? $cookieAttributes->withSameSite($sameSite)
            : $cookieAttributes->withoutSameSite();
    }
}
