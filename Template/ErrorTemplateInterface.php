<?php

declare(strict_types=1);

namespace Resonance\Template;

use Resonance\HttpError;
use Swoole\Http\Request;

interface ErrorTemplateInterface
{
    public function setError(Request $request, HttpError $httpError): void;
}
