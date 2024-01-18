<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum OllamaChatRole: string
{
    case Assistant = 'assistant';
    case System = 'system';
    case User = 'user';
}
