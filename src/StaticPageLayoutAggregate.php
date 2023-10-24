<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Generator;

readonly class StaticPageLayoutAggregate
{
    /**
     * @var Map<string,StaticPageLayoutInterface>
     */
    public Map $staticPageLayout;

    public function __construct()
    {
        $this->staticPageLayout = new Map();
    }

    /**
     * @return Generator<string>
     */
    public function render(StaticPage $staticPage): Generator
    {
        yield from $this
            ->selectLayout($staticPage)
            ->renderStaticPage($staticPage)
        ;
    }

    public function selectLayout(StaticPage $staticPage): StaticPageLayoutInterface
    {
        $layout = $staticPage->frontMatter->layout;

        if (!$this->staticPageLayout->hasKey($layout)) {
            throw new StaticPageReferenceException(sprintf(
                'Static page layout is not defined: "%s". Trying to render page: "%s"',
                $layout,
                $staticPage->getBasename(),
            ));
        }

        return $this->staticPageLayout->get($layout);
    }
}
