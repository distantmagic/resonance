<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use RuntimeException;
use Swoole\Http\Request;

readonly class SwooleServerRequestServer
{
    public function __construct(private Request $request) {}

    public function getServerVariable(string $name): string
    {
        if (!is_array($this->request->server)) {
            throw new RuntimeException('Server params are not set');
        }

        if (!array_key_exists($name, $this->request->server)) {
            throw new RuntimeException('Server variable is not set: '.$name);
        }

        if (!is_string($this->request->server[$name])) {
            throw new RuntimeException('Server variable is not a string: '.$name);
        }

        return $this->request->server[$name];
    }
}
