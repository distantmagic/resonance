<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class OAuth2UserSessionAuthenticated implements HttpInterceptableInterface
{
    /**
     * @psalm-taint-source file $templatePath
     */
    public function __construct(
        private ServerRequestInterface $request,
        private ResponseInterface $response,
    ) {}

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
