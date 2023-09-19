<?php

declare(strict_types=1);

namespace Resonance;

use Generator;
use IteratorAggregate;
use League\CommonMark\Extension\FrontMatter\Exception\InvalidFrontMatterException;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterParserInterface;
use League\CommonMark\Extension\FrontMatter\Input\MarkdownInputWithFrontMatter;
use Nette\Schema\ValidationException;
use Resonance\InputValidatedData\FrontMatter;
use Resonance\InputValidator\FrontMatterValidator;
use Resonance\StaticPageFileException\FrontMatterValidationException;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @template-implements IteratorAggregate<StaticPage>
 */
readonly class StaticPageIterator implements IteratorAggregate
{
    private FrontMatterParserInterface $frontMatterParser;
    private FrontMatterValidator $frontMatterValidator;

    public function __construct(private StaticPageFileIterator $fileIterator)
    {
        $frontMatterExtension = new FrontMatterExtension();

        $this->frontMatterParser = $frontMatterExtension->getFrontMatterParser();
        $this->frontMatterValidator = new FrontMatterValidator();
    }

    /**
     * @return Generator<StaticPage>
     */
    public function getIterator(): Generator
    {
        foreach ($this->fileIterator as $file) {
            $staticPage = $this->fileToStaticPage($file);

            yield $staticPage;
        }
    }

    private function fileToStaticPage(SplFileInfo $file): StaticPage
    {
        try {
            $result = $this->frontMatterParser->parse($file->getContents());

            return new StaticPage(
                $file,
                $this->resultToFrontMatter($file, $result),
                $result->getContent(),
            );
        } catch (InvalidFrontMatterException $exception) {
            throw new StaticPageFileException($file, $exception->getMessage(), $exception);
        }
    }

    private function resultToFrontMatter(
        SplFileInfo $file,
        MarkdownInputWithFrontMatter $result,
    ): FrontMatter {
        /**
         * @var mixed $frontMatter explicitly mixed for typechecks
         */
        $frontMatter = $result->getFrontMatter();

        if (is_null($frontMatter)) {
            throw new StaticPageFileException($file, 'File does not have a front matter');
        }

        try {
            return $this->frontMatterValidator->validateData($frontMatter);
        } catch (ValidationException $exception) {
            throw new FrontMatterValidationException($file, $exception);
        }
    }
}
