<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\InputValidatedData\FrontMatter;
use Symfony\Component\Finder\SplFileInfo;

readonly class StaticPage
{
    public function __construct(
        public SplFileInfo $file,
        public FrontMatter $frontMatter,
        private string $staticPagesOutputDirectory,
        public string $content,
    ) {}

    public function compare(self $other): int
    {
        if ($this->is($other)) {
            return 0;
        }

        return mb_strtolower($this->frontMatter->title) <=> mb_strtolower($other->frontMatter->title);
    }

    public function getBasename(): string
    {
        $relativePath = $this->file->getRelativePath();

        if (empty($relativePath)) {
            return $this->file->getFilenameWithoutExtension();
        }

        return sprintf(
            '%s/%s',
            $relativePath,
            $this->file->getFilenameWithoutExtension(),
        );
    }

    public function getHref(): string
    {
        if ('index' === $this->file->getFilenameWithoutExtension()) {
            $relativePath = $this->file->getRelativePath();

            if (empty($relativePath)) {
                return '/';
            }

            return '/'.$relativePath.'/';
        }

        return '/'.$this->getBasename().'.html';
    }

    public function getOutputDirectory(): string
    {
        return sprintf(
            '%s/%s',
            $this->staticPagesOutputDirectory,
            $this->file->getRelativePath(),
        );
    }

    public function getOutputPathname(): string
    {
        return sprintf(
            '%s/%s.html',
            $this->getOutputDirectory(),
            $this->file->getFilenameWithoutExtension(),
        );
    }

    public function is(self $other): bool
    {
        if ($this === $other) {
            return true;
        }

        return $this->getHref() === $other->getHref();
    }
}
