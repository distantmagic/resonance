<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface ErrorTemplateInterface
{
    public function renderHttpError(Request $request, Response $response, HttpError $httpError): HttpInterceptableInterface|HttpResponderInterface;
}
