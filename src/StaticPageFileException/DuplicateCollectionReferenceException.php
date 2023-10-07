<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\StaticPageFileException;

use Distantmagic\Resonance\FrontMatterCollectionReference;
use Distantmagic\Resonance\StaticPage;
use Distantmagic\Resonance\StaticPageFileException;

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
