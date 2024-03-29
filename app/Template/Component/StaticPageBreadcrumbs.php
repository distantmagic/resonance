<?php

declare(strict_types=1);

namespace Distantmagic\Docs\Template\Component;

use Distantmagic\Docs\Template\Component;
use Distantmagic\Resonance\StaticPage;
use Distantmagic\Resonance\StaticPageParentIterator;
use Ds\Map;
use Generator;

final readonly class StaticPageBreadcrumbs extends Component
{
    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(private Map $staticPages) {}

    /**
     * @return Generator<string>
     */
    public function render(StaticPage $staticPage): Generator
    {
        foreach (new StaticPageParentIterator($this->staticPages, $staticPage) as $parentPage) {
            if ($parentPage->is($staticPage)) {
                yield sprintf(
                    '<span class="breadcrumb active">%s</span>',
                    $parentPage->frontMatter->title,
                );
            } else {
                yield sprintf(
                    '<a class="breadcrumb" href="%s">%s</a>',
                    $parentPage->getHref(),
                    $parentPage->frontMatter->title,
                );
            }
        }
    }
}
