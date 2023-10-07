<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpRequestLanguageDetector;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\SupportedLanguageCodeRepositoryInterface;
use Distantmagic\Resonance\TranslationsLoader;
use Distantmagic\Resonance\Translator;

/**
 * @template-extends SingletonProvider<Translator>
 */
#[Singleton(provides: Translator::class)]
final readonly class TranslatorProvider extends SingletonProvider
{
    public function __construct(
        private HttpRequestLanguageDetector $languageDetector,
        private SupportedLanguageCodeRepositoryInterface $supportedLanguageCodeRepository,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Translator
    {
        $translator = new Translator($this->languageDetector);

        foreach ($this->supportedLanguageCodeRepository->cases() as $language) {
            $filename = DM_APP_ROOT.'/lang/'.$language->getName().'/website.ini';
            $translator->translations->put($language, TranslationsLoader::load($filename));
        }

        return $translator;
    }
}
