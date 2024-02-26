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
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Swoole\Http\Response;
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
        Response $response,
        Throwable $throwable,
    ): null|HttpInterceptableInterface|HttpResponderInterface {
        if (!$response->isWritable()) {
            throw new RuntimeException('Server response in not writable. Unable to report error', 0, $throwable);
        }

        if (Environment::Development !== $this->applicationConfiguration->environment) {
            return $this->respond($request, $response);
        }

        $response->status(500);
        $response->header('content-type', ContentType::TextPlain->value);
        $response->end((string) $throwable);

        return null;
    }
}
