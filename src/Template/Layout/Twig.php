<?php

declare(strict_types=1);

namespace Resonance\Template\Layout;

use Resonance\Attribute\Singleton;
use Resonance\ContentType;
use Resonance\HttpResponderInterface;
use Resonance\SecurityPolicyHeaders;
use Resonance\Template\Layout;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Twig\Environment;
use WeakMap;

#[Singleton]
final readonly class Twig extends Layout
{
    /**
     * @var WeakMap<Request,string>
     */
    private WeakMap $templates;

    public function __construct(
        private Environment $twig,
        private SecurityPolicyHeaders $securityPolicyHeaders,
    ) {
        /**
         * @var WeakMap<Request,string>
         */
        $this->templates = new WeakMap();
    }

    public function getContentType(Request $request, Response $response): ContentType
    {
        return ContentType::TextHtml;
    }

    public function render(Request $request, Response $response, string $templatePath, ?array $templateData = null): self
    {
        $this->templates->offsetSet(
            $request,
            $this->twig->render($templatePath, $templateData ?? [
                'request' => $request,
                'response' => $response,
            ])
        );

        return $this;
    }

    public function respond(Request $request, Response $response): ?HttpResponderInterface
    {
        $this->sendContentTypeHeader($request, $response);
        $this->securityPolicyHeaders->sendTemplatedPagePolicyHeaders($request, $response);

        $response->end($this->templates->offsetGet($request));

        return null;
    }
}
