<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TwigTemplate implements HttpInterceptableInterface
{
    /**
     * @psalm-taint-source file $templatePath
     */
    public function __construct(
        private ServerRequestInterface $request,
        private ResponseInterface $response,
        private string $templatePath,
        private array $templateData = [],
    ) {}

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return $this->request;
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
