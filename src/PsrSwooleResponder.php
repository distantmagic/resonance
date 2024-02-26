<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

#[Singleton]
readonly class PsrSwooleResponder
{
    public function respondWithPsrResponse(
        Response $response,
        ResponseInterface $psrResponse,
    ): void {
        /**
         * @var null|string $sendfile
         */
        $sendfile = null;

        foreach ($psrResponse->getHeaders() as $name => $values) {
            $headerLine = implode(', ', $values);

            if ('x-sendfile' !== $name) {
                $response->header((string) $name, $headerLine);
            } else {
                $sendfile = $headerLine;
            }
        }

        if (is_string($sendfile)) {
            if (!$response->sendfile($sendfile)) {
                $response->status(500, 'Unable to send file');
            }
        } else {
            $response->status(
                $psrResponse->getStatusCode(),
                $psrResponse->getReasonPhrase(),
            );
            $response->end($psrResponse->getBody()->getContents());
        }
    }
}
