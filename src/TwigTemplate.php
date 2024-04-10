<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TwigTemplate implements HttpInterceptableInterface
{
    private SwooleContextRequestResponseReader $swooleContextRequestResponseReader;

    /**
     * @psalm-taint-source file $templatePath
     */
    public function __construct(
        private string $templatePath,
        private array $templateData = [],
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

    public function getTemplateData(ServerRequestInterface $request, ResponseInterface $response): array
    {
        return $this->templateData + [
            'request' => $request,
            'response' => $response,
        ];
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }
}
