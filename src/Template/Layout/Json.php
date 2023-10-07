<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Template\Layout;

use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SecurityPolicyHeaders;
use Distantmagic\Resonance\Template\Layout;
use JsonSerializable;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class Json extends Layout
{
    abstract protected function renderJson(Request $request, Response $response): array|JsonSerializable;

    public function __construct(private SecurityPolicyHeaders $securityPolicyHeaders) {}

    public function getContentType(Request $request, Response $response): ContentType
    {
        return ContentType::ApplicationJson;
    }

    public function respond(Request $request, Response $response): ?HttpResponderInterface
    {
        $this->sendContentTypeHeader($request, $response);
        $this->securityPolicyHeaders->sendJsonPagePolicyHeaders($response);

        $response->end(json_encode(
            value: $this->renderJson($request, $response),
            flags: JSON_THROW_ON_ERROR,
        ));

        return null;
    }
}
