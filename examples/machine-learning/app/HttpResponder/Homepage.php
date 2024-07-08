<?php

declare(strict_types=1);

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\TwigTemplate;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/',
    routeSymbol: HttpRouteSymbol::Homepage,
)]
function Homepage(): HttpInterceptableInterface
{
    return new TwigTemplate('homepage.twig');
}
