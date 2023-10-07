<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidatedData;

use Distantmagic\Resonance\FrontMatterCollectionReference;
use Distantmagic\Resonance\InputValidatedData;
use Distantmagic\Resonance\StaticPageContentType;
use Distantmagic\Resonance\StaticPageLayoutHandler;

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
