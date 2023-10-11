<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Template;

use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\Template;
use Distantmagic\Resonance\TemplateLayoutInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class Layout extends Template implements TemplateLayoutInterface
{
    abstract public function getContentType(Request $request, Response $response): ContentType;

    public function getCharset(): string
    {
        return 'utf-8';
    }

    protected function sendContentTypeHeader(Request $request, Response $response): void
    {
        $response->header(
            'content-type',
            sprintf(
                '%s;charset=%s',
                $this->getContentType($request, $response)->value,
                $this->getCharset(),
            )
        );
    }
}
