<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum LlamaCppHealthStatus: string
{
    case Error = 'error';
    case LoadingModel = 'loading model';
    case Ok = 'ok';
}
