<?php

declare(strict_types=1);

namespace Resonance;

use IteratorAggregate;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Traversable;

/**
 * @template-implements IteratorAggregate<SplFileInfo>
 */
readonly class PHPFileIterator implements IteratorAggregate
{
    public function __construct(private string $baseDirectory) {}

    public function getIterator(): Traversable
    {
        $finder = new Finder();

        return $finder
            ->files()
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('*Test.php')
            ->in($this->baseDirectory)
        ;
    }
}
