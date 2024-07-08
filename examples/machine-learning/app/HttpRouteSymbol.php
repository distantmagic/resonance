<?php

declare(strict_types=1);

namespace App;

use Distantmagic\Resonance\CastableEnumTrait;
use Distantmagic\Resonance\HttpRouteSymbolInterface;
use Distantmagic\Resonance\NameableEnumTrait;

enum HttpRouteSymbol implements HttpRouteSymbolInterface
{
    use CastableEnumTrait;
    use NameableEnumTrait;

    case Homepage;
}
