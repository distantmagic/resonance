<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton(provides: JsonErrorTemplateInterface::class)]
readonly class JsonErrorTemplate implements JsonErrorTemplateInterface
{
    public function renderHttpError(Request $request, Response $response, HttpError $httpError): HttpInterceptableInterface
    {
        return new JsonTemplate([
            'code' => $httpError->code(),
            'message' => $httpError->message($request),
        ]);
    }
}
