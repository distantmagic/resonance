<?php

declare(strict_types=1);

namespace Resonance;

enum CrudAction
{
    case Delete;
    case Read;
    case Update;
}
