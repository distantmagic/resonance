<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;

interface ErrorTemplateInterface extends TemplateInterface, TemplateWriterInterface
{
    public function setError(Request $request, HttpError $httpError): void;
}
