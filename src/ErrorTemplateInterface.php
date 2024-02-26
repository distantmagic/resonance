<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorTemplateInterface
{
    public function renderHttpError(ServerRequestInterface $request, ResponseInterface $response, HttpError $httpError): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface;
}
