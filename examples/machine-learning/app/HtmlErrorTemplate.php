<?php

declare(strict_types=1);

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HtmlErrorTemplateInterface;
use Distantmagic\Resonance\HttpError;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\TwigTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton(provides: HtmlErrorTemplateInterface::class)]
readonly class HtmlErrorTemplate implements HtmlErrorTemplateInterface
{
    public function renderHttpError(ServerRequestInterface $request, ResponseInterface $response, HttpError $httpError): HttpInterceptableInterface
    {
        return new TwigTemplate('error.twig', [
            'error' => $httpError,
        ]);
    }
}
