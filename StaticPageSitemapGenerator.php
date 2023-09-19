<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;

readonly class StaticPageSitemapGenerator
{
    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(private Map $staticPages) {}

    public function writeTo(string $filename): void
    {
        $baseUrl = DM_STATIC_BASE_URL;
        $fhandle = fopen($filename, 'w');

        try {
            fwrite($fhandle, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
            fwrite($fhandle, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");

            foreach ($this->staticPages as $staticPage) {
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
