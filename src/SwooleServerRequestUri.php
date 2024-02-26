<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;
use Psr\Http\Message\UriInterface;

readonly class SwooleServerRequestUri implements UriInterface
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private SwooleServerRequestServer $server,
        private SwooleConfiguration $swooleConfiguration,
    ) {}

    public function __toString(): string
    {
        return sprintf(
            '%s://%s%s',
            $this->getScheme(),
            $this->getAuthority(),
            $this->getPath(),
        );
    }

    public function getAuthority(): string
    {
        return sprintf(
            '%s:%d',
            $this->swooleConfiguration->host,
            $this->swooleConfiguration->port,
        );
    }

    public function getFragment(): string
    {
        return '';
    }

    public function getHost(): string
    {
        return $this->swooleConfiguration->host;
    }

    public function getPath(): string
    {
        return $this->server->getServerVariable('request_uri');
    }

    public function getPort(): int
    {
        return $this->swooleConfiguration->port;
    }

    public function getQuery(): string
    {
        return $this->server->getServerVariable('query_string');
    }

    public function getScheme(): string
    {
        return $this->applicationConfiguration->scheme;
    }

    public function getUserInfo(): string
    {
        return '';
    }

    public function withFragment($fragment): never
    {
        $this->throwNotExtendable();
    }

    public function withHost($host): never
    {
        $this->throwNotExtendable();
    }

    public function withPath($path): never
    {
        $this->throwNotExtendable();
    }

    public function withPort($port): never
    {
        $this->throwNotExtendable();
    }

    public function withQuery($query): never
    {
        $this->throwNotExtendable();
    }

    public function withScheme($scheme): never
    {
        $this->throwNotExtendable();
    }

    public function withUserInfo($user, $password = null): never
    {
        $this->throwNotExtendable();
    }

    private function throwNotExtendable(): never
    {
        throw new LogicException('This request is not extendable');
    }
}
