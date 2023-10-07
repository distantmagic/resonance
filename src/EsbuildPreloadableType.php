<?php

declare(strict_types=1);

namespace Resonance;

enum EsbuildPreloadableType
{
    case Font;
    case Image;
    case JavaScriptModule;
    case Stylesheet;
}
