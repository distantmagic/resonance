<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\InputValidatedData\FrontMatter;
use Distantmagic\Resonance\InputValidator\FrontMatterValidator;
use Distantmagic\Resonance\StaticPageFileException\FrontMatterValidationException;
use Generator;
use IteratorAggregate;
use League\CommonMark\Extension\FrontMatter\Exception\InvalidFrontMatterException;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterParserInterface;
use League\CommonMark\Extension\FrontMatter\Input\MarkdownInputWithFrontMatter;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @template-implements IteratorAggregate<StaticPage>
 */
readonly class StaticPageIterator implements IteratorAggregate
{
    private FrontMatterParserInterface $frontMatterParser;

    public function __construct(
        private FrontMatterValidator $frontMatterValidator,
        private InputValidatorController $inputValidatorController,
        private StaticPageFileIterator $fileIterator,
        private string $staticPagesOutputDirectory,
    ) {
        $frontMatterExtension = new FrontMatterExtension();

        $this->frontMatterParser = $frontMatterExtension->getFrontMatterParser();
    }

    /**
     * @return Generator<StaticPage>
     */
    public function getIterator(): Generator
    {
        foreach ($this->fileIterator as $file) {
            $staticPage = $this->fileToStaticPage($file);

            if (!$staticPage->frontMatter->isDraft) {
                yield $staticPage;
            }
        }
    }

    private function fileToStaticPage(SplFileInfo $file): StaticPage
    {
        try {
            $result = $this->frontMatterParser->parse($file->getContents());

            return new StaticPage(
                $file,
                $this->resultToFrontMatter($file, $result),
                $this->staticPagesOutputDirectory,
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

        $inputValidationResult = $this->inputValidatorController->validateData(
            $this->frontMatterValidator,
            $frontMatter,
        );

        if ($inputValidationResult->inputValidatedData) {
            return $inputValidationResult->inputValidatedData;
        }

        throw new FrontMatterValidationException($file, $inputValidationResult->getErrorMessage());
    }
}
