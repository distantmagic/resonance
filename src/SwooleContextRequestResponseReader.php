<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Assert\Assertion;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class SwooleContextRequestResponseReader
{
    public const CONTEXT_KEY_REQUEST = 'psr_http_request';
    public const CONTEXT_KEY_RESPONSE = 'psr_http_response';

    private ServerRequestInterface $request;
    private ResponseInterface $response;

    public function __construct(
        ?ServerRequestInterface $request = null,
        ?ResponseInterface $response = null,
    ) {
        if ($request && $response) {
            $this->request = $request;
            $this->response = $response;

            return;
        }

        $context = SwooleCoroutineHelper::mustGetContext();

        /**
         * @var mixed explicitly mixed for typechecks
         */
        $request ??= $context[self::CONTEXT_KEY_REQUEST];

        Assertion::isInstanceOf($request, ServerRequestInterface::class);

        /**
         * @var mixed explicitly mixed for typechecks
         */
        $response ??= $context[self::CONTEXT_KEY_RESPONSE];

        Assertion::isInstanceOf($response, ResponseInterface::class);

        $this->request = $request;
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
