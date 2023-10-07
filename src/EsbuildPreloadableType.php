<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum EsbuildPreloadableType
{
    case Font;
    case Image;
    case JavaScriptModule;
    case Stylesheet;
}
