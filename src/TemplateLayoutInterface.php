<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface TemplateLayoutInterface extends TemplateInterface, TemplateWriterInterface
{
    public function sendContentTypeHeader(Request $request, Response $response): void;
}
