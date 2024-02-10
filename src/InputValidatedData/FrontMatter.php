<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidatedData;

use Distantmagic\Resonance\FrontMatterCollectionReference;
use Distantmagic\Resonance\InputValidatedData;
use Distantmagic\Resonance\StaticPageContentType;

/**
 * @psalm-suppress PossiblyUnusedProperty used in templates
 */
readonly class FrontMatter extends InputValidatedData
{
    /**
     * @param array<FrontMatterCollectionReference> $collections
     * @param non-empty-string                      $description
     * @param non-empty-string                      $layout
     * @param null|non-empty-string                 $next
     * @param null|non-empty-string                 $parent
     * @param list<non-empty-string>                $registerStylesheets
     * @param list<non-empty-string>                $tags
     * @param non-empty-string                      $title
     */
    public function __construct(
        public array $collections,
        public StaticPageContentType $contentType,
        public string $description,
        public bool $isDraft,
        public string $layout,
        public ?string $next,
        public ?string $parent,
        public array $registerStylesheets,
        public array $tags,
        public string $title,
    ) {}
}
