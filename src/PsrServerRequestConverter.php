<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Swoole\Http\Request;

#[Singleton]
readonly class PsrServerRequestConverter
{
    private ServerRequestCreator $serverRequestCreator;

    public function __construct(Psr17Factory $psr17Factory)
    {
        $this->serverRequestCreator = new ServerRequestCreator(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
        );
    }

    public function convertToServerRequest(Request $request): ServerRequestInterface
    {
        if (!is_array($request->server)) {
            throw new RuntimeException('Server variables are not set.');
        }

        $serverUppercase = [];

        /**
         * @var string $value
         */
        foreach ($request->server as $key => $value) {
            $serverUppercase[mb_strtoupper((string) $key)] = $value;
        }

        return $this->serverRequestCreator->fromArrays(
            $serverUppercase,
            is_array($request->header) ? $request->header : [],
            is_array($request->cookie) ? $request->cookie : [],
            is_array($request->get) ? $request->get : [],
            is_array($request->post) ? $request->post : [],
            is_array($request->files) ? $request->files : [],
            $request->getContent() ?: null,
        );
    }
}
