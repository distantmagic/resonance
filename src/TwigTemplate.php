<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\ContentSecurityPolicy;
use Distantmagic\Resonance\Attribute\InterceptableTwigTemplate;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[ContentSecurityPolicy(ContentSecurityPolicyType::Html)]
#[InterceptableTwigTemplate]
final readonly class TwigTemplate implements HttpInterceptableInterface
{
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
