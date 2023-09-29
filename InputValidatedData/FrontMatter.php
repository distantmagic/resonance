<?php

declare(strict_types=1);

namespace Resonance\InputValidatedData;

use Resonance\FrontMatterCollectionReference;
use Resonance\InputValidatedData;
use Resonance\StaticPageContentType;
use Resonance\StaticPageLayoutHandler;

readonly class FrontMatter extends InputValidatedData
{
    /**
     * @param array<FrontMatterCollectionReference> $collections
     * @param array<string>                         $registerStylesheets
     */
    public function __construct(
        public array $collections,
        public StaticPageContentType $contentType,
        public string $description,
        public bool $isDraft,
        public StaticPageLayoutHandler $layout,
        public ?string $next,
        public ?string $parent,
        public array $registerStylesheets,
        public string $title,
    ) {}
}
