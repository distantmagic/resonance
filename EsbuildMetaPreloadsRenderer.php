<?php

declare(strict_types=1);

namespace Resonance;

readonly class EsbuildMetaPreloadsRenderer
{
    public function __construct(
        private EsbuildMetaEntryPoints $esbuildMetaEntryPoints,
        private TemplateFilters $filters,
    ) {}

    public function render(): string
    {
        $preloadables = new EsbuildPreloadablesIterator($this->esbuildMetaEntryPoints);

        $ret = '';

        foreach (new EsbuildPreloadablesPriorityIterator($preloadables) as $preloadable) {
            $href = $this->filters->escape($preloadable->pathname);

            $ret .= sprintf(
                match ($preloadable->type) {
                    EsbuildPreloadableType::Font => '<link rel="preload" as="font" href="%s" crossorigin>'."\n",
                    EsbuildPreloadableType::Image => '<link rel="preload" as="image" href="%s">'."\n",
                    EsbuildPreloadableType::JavaScriptModule => '<link rel="modulepreload" href="%s">'."\n",
                    EsbuildPreloadableType::Stylesheet => '<link rel="preload" as="style" href="%s">'."\n",
                },
                '/'.$href,
            );
        }

        return $ret;
    }
}
