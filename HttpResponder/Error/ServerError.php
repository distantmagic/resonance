<?php

declare(strict_types=1);

namespace Resonance\HttpResponder\Error;

use Psr\Log\LoggerInterface;
use Resonance\Attribute\Singleton;
use Resonance\Environment;
use Resonance\ErrorHttpResponderDependencies;
use Resonance\HttpError\ServerError as ServerErrorEntity;
use Resonance\HttpResponder\Error;
use RuntimeException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Throwable;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run as Whoops;

use function Sentry\captureException;

#[Singleton]
final readonly class ServerError extends Error
{
    private PlainTextHandler $handler;
    private Whoops $whoops;

    public function __construct(
        ErrorHttpResponderDependencies $errorHttpResponderDependencies,
        private LoggerInterface $logger,
        ServerErrorEntity $httpError,
    ) {
        parent::__construct($errorHttpResponderDependencies, $httpError);

        $this->handler = new PlainTextHandler();

        $this->whoops = new Whoops();
        $this->whoops->allowQuit(false);
        $this->whoops->writeToOutput(false);
        $this->whoops->pushHandler($this->handler);
    }

    public function respondWithThrowable(
        Request $request,
        Response $response,
        Throwable $throwable,
    ): void {
        captureException($throwable);

        if (!$response->isWritable()) {
            throw new RuntimeException('Server response in not writable. Unable to report error', 0, $throwable);
        }

        if (Environment::Development !== DM_APP_ENV) {
            $this->respond($request, $response);

            return;
        }

        $message = $this->whoops->handleException($throwable);

        $this->logger->error($message);

        $response->status(500);
        $response->header('content-type', $this->handler->contentType());
        $response->end($message);
    }
}
