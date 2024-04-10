<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use PhpToken;
use SplFileInfo;

/**
 * @template-implements IteratorAggregate<PhpToken>
 */
readonly class PHPFileTokenIterator implements IteratorAggregate
{
    public function __construct(
        private SplFileInfo $file,
    ) {}

    /**
     * @return Generator<PhpToken>
     */
    public function getIterator(): Generator
    {
        $content = file_get_contents($this->file->getPathname());

        foreach (PhpToken::tokenize($content) as $phpToken) {
            yield $phpToken;
        }
    }
}
