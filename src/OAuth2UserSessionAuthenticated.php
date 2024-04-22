<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class OAuth2UserSessionAuthenticated implements HttpInterceptableInterface
{
    private SwooleContextRequestResponseReader $swooleContextRequestResponseReader;

    /**
     * @psalm-taint-source file $templatePath
     */
    public function __construct(
        ?ServerRequestInterface $request = null,
        ?ResponseInterface $response = null,
    ) {
        $this->swooleContextRequestResponseReader = new SwooleContextRequestResponseReader(
            request: $request,
            response: $response,
        );
    }

    public function getResponse(): ResponseInterface
    {
        return $this->swooleContextRequestResponseReader->getResponse();
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return $this->swooleContextRequestResponseReader->getServerRequest();
    }
}
