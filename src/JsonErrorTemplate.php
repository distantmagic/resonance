<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton(provides: JsonErrorTemplateInterface::class)]
readonly class JsonErrorTemplate implements JsonErrorTemplateInterface
{
    public function renderHttpError(ServerRequestInterface $request, ResponseInterface $response, HttpError $httpError): HttpInterceptableInterface
    {
        return new JsonTemplate([
            'code' => $httpError->code(),
            'message' => $httpError->message($request),
        ]);
    }
}
