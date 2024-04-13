<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

readonly class EsbuildMetaPreloadsRenderer implements Stringable
{
    public function __construct(private EsbuildMetaEntryPoints $esbuildMetaEntryPoints) {}

    public function __toString(): string
    {
        return $this->render();
    }

    public function render(): string
    {
        $preloadables = new EsbuildPreloadablesIterator($this->esbuildMetaEntryPoints);

        $ret = '';

        foreach ($preloadables as $type => $pathname) {
            $ret .= sprintf(
                match ($type) {
                    EsbuildPreloadableType::Font => '<link rel="preload" as="font" href="%s" crossorigin>'."\n",
                    EsbuildPreloadableType::Image => '<link rel="preload" as="image" href="%s">'."\n",
                    EsbuildPreloadableType::JavaScriptModule => '<link rel="modulepreload" href="%s">'."\n",
                    EsbuildPreloadableType::Stylesheet => '<link rel="preload" as="style" href="%s">'."\n",
                },
                $this->prefixPathname($pathname),
            );
        }

        return $ret;
    }

    private function prefixPathname(string $pathname): string
    {
        if (filter_var($pathname, FILTER_VALIDATE_URL)) {
            return $pathname;
        }

        return '/'.$pathname;
    }
}
