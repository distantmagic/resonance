<?php

declare(strict_types=1);

namespace Resonance\Template;

use Resonance\ContentType;
use Resonance\Template;
use Resonance\TemplateLayoutInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class Layout extends Template implements TemplateLayoutInterface
{
    abstract public function getContentType(Request $request, Response $response): ContentType;

    protected function sendContentTypeHeader(Request $request, Response $response): void
    {
        $response->header(
            'content-type',
            $this->getContentType($request, $response)->value,
        );
    }
}
