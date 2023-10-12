<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\Error;

use Distantmagic\Resonance\ApplicationContext;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\ErrorHttpResponderDependencies;
use Distantmagic\Resonance\HttpError\ServerError as ServerErrorEntity;
use Distantmagic\Resonance\HttpResponder\Error;
use Distantmagic\Resonance\HttpResponderInterface;
use RuntimeException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Throwable;

#[Singleton]
final readonly class ServerError extends Error
{
    public function __construct(
        private ApplicationContext $applicationContext,
        ErrorHttpResponderDependencies $errorHttpResponderDependencies,
        ServerErrorEntity $httpError,
    ) {
        parent::__construct($errorHttpResponderDependencies, $httpError);
    }

    public function respondWithThrowable(
        Request $request,
        Response $response,
        Throwable $throwable,
    ): ?HttpResponderInterface {
        if (!$response->isWritable()) {
            throw new RuntimeException('Server response in not writable. Unable to report error', 0, $throwable);
        }

        if (Environment::Development !== $this->applicationContext->environment) {
            return $this->respond($request, $response);
        }

        $response->status(500);
        $response->header('content-type', ContentType::TextPlain->value);
        $response->end((string) $throwable);

        return null;
    }
}
