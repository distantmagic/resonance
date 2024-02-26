<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

interface ErrorTemplateInterface
{
    public function renderHttpError(ServerRequestInterface $request, Response $response, HttpError $httpError): HttpInterceptableInterface|HttpResponderInterface;
}
