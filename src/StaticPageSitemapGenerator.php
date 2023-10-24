<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class StaticPageSitemapGenerator
{
    public function __construct(
        private StaticPageAggregate $staticPageAggregate,
        private StaticPageConfiguration $staticPageConfiguration,
    ) {}

    public function writeTo(string $filename): void
    {
        $baseUrl = $this->staticPageConfiguration->baseUrl;
        $fhandle = fopen($filename, 'w');

        try {
            fwrite($fhandle, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
            fwrite($fhandle, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");

            foreach ($this->staticPageAggregate->staticPages as $staticPage) {
                fwrite($fhandle, sprintf(
                    "<url><loc>%s</loc><lastmod>%s</lastmod></url>\n",
                    $baseUrl.$staticPage->getHref(),
                    date('Y-m-d', $staticPage->file->getMTime()),
                ));
            }

            fwrite($fhandle, "</urlset>\n");
        } finally {
            fclose($fhandle);
        }
    }
}
