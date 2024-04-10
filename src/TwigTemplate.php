<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TwigTemplate implements HttpInterceptableInterface
{
    private ServerRequestInterface $request;
    private ResponseInterface $response;

    /**
     * @psalm-taint-source file $templatePath
     */
    public function __construct(
        private string $templatePath,
        private array $templateData = [],
        ?ResponseInterface $response = null,
    ) {
        $context = SwooleCoroutineHelper::mustGetContext();

        $this->request = $context['psr_http_request'];
        $this->response = $response ?? $context['psr_http_response'];
    }

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
