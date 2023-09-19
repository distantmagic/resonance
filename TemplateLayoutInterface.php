<?php

declare(strict_types=1);

namespace Resonance;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface TemplateLayoutInterface extends TemplateInterface, TemplateWriterInterface
{
    public function getContentType(Request $request, Response $response): ContentType;
}
