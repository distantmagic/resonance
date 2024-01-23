<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\InputValidator\FrontMatterValidator;
use Distantmagic\Resonance\InputValidatorController;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\StaticPageAggregate;
use Distantmagic\Resonance\StaticPageConfiguration;
use Distantmagic\Resonance\StaticPageFileIterator;
use Distantmagic\Resonance\StaticPageIterator;

/**
 * @template-extends SingletonProvider<StaticPageAggregate>
 */
#[Singleton(provides: StaticPageAggregate::class)]
final readonly class StaticPageAggregateProvider extends SingletonProvider
{
    public function __construct(
        private FrontMatterValidator $frontMatterValidator,
        private InputValidatorController $inputValidatorController,
        private StaticPageConfiguration $staticPageConfiguration,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): StaticPageAggregate
    {
        $fileIterator = new StaticPageFileIterator($this->staticPageConfiguration->inputDirectory);
        $staticPageIterator = new StaticPageIterator(
            $this->frontMatterValidator,
            $this->inputValidatorController,
            $fileIterator,
            $this->staticPageConfiguration->outputDirectory,
        );

        $staticPageAggregate = new StaticPageAggregate();

        foreach ($staticPageIterator as $staticPage) {
            $staticPageAggregate->staticPages->put(
                $staticPage->getBasename(),
                $staticPage,
            );
        }

        return $staticPageAggregate;
    }
}
