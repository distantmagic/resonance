<?php

declare(strict_types=1);

namespace Resonance\HttpResponder\Error;

use Psr\Log\LoggerInterface;
use Resonance\Attribute\Singleton;
use Resonance\ContentType;
use Resonance\Environment;
use Resonance\ErrorHttpResponderDependencies;
use Resonance\HttpError\ServerError as ServerErrorEntity;
use Resonance\HttpResponder\Error;
use Resonance\HttpResponderInterface;
use RuntimeException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Throwable;

#[Singleton]
final readonly class ServerError extends Error
{
    public function __construct(
        ErrorHttpResponderDependencies $errorHttpResponderDependencies,
        private LoggerInterface $logger,
        ServerErrorEntity $httpError,
    ) {
        parent::__construct($errorHttpResponderDependencies, $httpError);
    }

    public function respondWithThrowable(
        Request $request,
        Response $response,
        Throwable $throwable,
    ): ?HttpResponderInterface {
        $this->logger->error((string) $throwable);

        if (!$response->isWritable()) {
            throw new RuntimeException('Server response in not writable. Unable to report error', 0, $throwable);
        }

        if (Environment::Development !== DM_APP_ENV) {
            return $this->respond($request, $response);
        }

        $response->status(500);
        $response->header('content-type', ContentType::TextPlain->value);
        $response->end((string) $throwable);

        return null;
    }
}
