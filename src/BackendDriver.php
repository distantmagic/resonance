<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum BackendDriver
{
    case Amp;
    case Swoole;
}
