<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\ContentSecurityPolicy;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stringable;

#[ContentSecurityPolicy(ContentSecurityPolicyType::Json)]
final readonly class JsonTemplate implements HttpInterceptableInterface
{
    public function __construct(
        private ServerRequestInterface $request,
        private ResponseInterface $response,
        public array|JsonSerializable|string|Stringable $data,
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
