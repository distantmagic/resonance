<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpInterceptableInterface
{
    public function getResponse(): ResponseInterface;

    public function getServerRequest(): ServerRequestInterface;
}
