<?php

declare(strict_types=1);

namespace Resonance\StaticPageFileException;

use Resonance\FrontMatterCollectionReference;
use Resonance\StaticPage;
use Resonance\StaticPageFileException;

class DuplicateCollectionReferenceException extends StaticPageFileException
{
    public function __construct(
        StaticPage $staticPage,
        FrontMatterCollectionReference $frontMatterCollection
    ) {
        parent::__construct(
            message: sprintf(
                'Staic page is added to the same collection multiple times: %s',
                $frontMatterCollection->name,
            ),
            splFileInfo: $staticPage->file,
        );
    }
}
