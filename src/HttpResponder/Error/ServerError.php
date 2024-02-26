<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\Error;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\ErrorHttpResponderDependencies;
use Distantmagic\Resonance\HttpError\ServerError as ServerErrorEntity;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder\Error;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PsrStringStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

#[Singleton]
final readonly class ServerError extends Error
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        ErrorHttpResponderDependencies $errorHttpResponderDependencies,
        ServerErrorEntity $httpError,
    ) {
        parent::__construct($errorHttpResponderDependencies, $httpError);
    }

    public function sendThrowable(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Throwable $throwable,
    ): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface {
        if (Environment::Development !== $this->applicationConfiguration->environment) {
            return $this->respond($request, $response);
        }

        return $response
            ->withStatus(500)
            ->withHeader('content-type', ContentType::TextPlain->value)
            ->withBody(new PsrStringStream((string) $throwable))
        ;
    }
}
