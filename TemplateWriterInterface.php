<?php

declare(strict_types=1);

namespace Resonance;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface TemplateWriterInterface
{
    public function write(Request $request, Response $response): void;
}
