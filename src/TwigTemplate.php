<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;
use Swoole\Http\Response;

final readonly class TwigTemplate implements HttpInterceptableInterface
{
    /**
     * @psalm-taint-source file $templatePath
     */
    public function __construct(
        private string $templatePath,
        private array $templateData = [],
    ) {}

    public function getTemplateData(Request $request, Response $response): array
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
