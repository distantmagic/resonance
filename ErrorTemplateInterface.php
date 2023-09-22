<?php

declare(strict_types=1);

namespace Resonance;

use Swoole\Http\Request;

interface ErrorTemplateInterface extends TemplateInterface, TemplateWriterInterface
{
    public function setError(Request $request, HttpError $httpError): void;
}
