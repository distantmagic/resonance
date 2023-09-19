<?php

declare(strict_types=1);

namespace Resonance\InputValidatedData;

use App\InputValidatedData;
use Resonance\FrontMatterCollectionReference;
use Resonance\StaticPageContentType;
use Resonance\StaticPageLayoutHandler;

readonly class FrontMatter extends InputValidatedData
{
    /**
     * @param array<FrontMatterCollectionReference> $collections
     */
    public function __construct(
        public array $collections,
        public StaticPageContentType $contentType,
        public string $description,
        public StaticPageLayoutHandler $layout,
        public ?string $parent,
        public string $title,
    ) {}
}
