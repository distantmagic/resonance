<?php

declare(strict_types=1);

namespace Resonance\Template;

use App\Template;
use Resonance\TemplateStaticPageLayoutInterface;

abstract readonly class StaticPageLayout extends Template implements TemplateStaticPageLayoutInterface {}
