<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Template\Layout;

use Distantmagic\Resonance\Attribute\ContentSecurityPolicy;
use Distantmagic\Resonance\Attribute\RenderableTwigTemplate;
use Distantmagic\Resonance\ContentSecurityPolicyType;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpPreprocessor\TwigTemplateRendererPreprocessor;
use Distantmagic\Resonance\Template\Layout;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[ContentSecurityPolicy(ContentSecurityPolicyType::Html)]
#[RenderableTwigTemplate]
final readonly class Twig extends Layout
{
    public function __construct(
        private string $templatePath,
        private array $templateData = [],
    ) {}

    public function getContentType(Request $request, Response $response): ContentType
    {
        return ContentType::TextHtml;
    }

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

    public function respond(Request $request, Response $response): never
    {
        throw new LogicException(sprintf(
            'Twig layout should be intercepted by the %s',
            TwigTemplateRendererPreprocessor::class,
        ));
    }
}
