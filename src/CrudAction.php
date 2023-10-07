<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum CrudAction
{
    case Delete;
    case Read;
    case Update;
}
