<?php

declare(strict_types=1);

namespace Resonance;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface TemplateLayoutInterface extends TemplateInterface, TemplateWriterInterface
{
    /**
     * @todo remove after factoring
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getContentType(Request $request, Response $response): ContentType;
}
