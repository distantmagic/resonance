<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Symfony\Component\Routing\Route;

readonly class RespondsToHttpAttributeRoute
{
    public Route $symfonyRoute;

    public function __construct(RespondsToHttp $attribute)
    {
        $this->symfonyRoute = new Route($attribute->pattern);
        $this->symfonyRoute->setMethods($attribute->method->value);

        if ($attribute->requirements) {
            $this->symfonyRoute->setRequirements($attribute->requirements);
        }

        $this->symfonyRoute->compile();
    }
}
