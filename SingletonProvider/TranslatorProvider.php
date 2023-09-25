<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\Singleton;
use Resonance\HttpRequestLanguageDetector;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Resonance\SupportedLanguageCodeRepositoryInterface;
use Resonance\TranslationsLoader;
use Resonance\Translator;

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

    public function provide(SingletonContainer $singletons): Translator
    {
        $translator = new Translator($this->languageDetector);

        foreach ($this->supportedLanguageCodeRepository->cases() as $language) {
            $filename = DM_APP_ROOT.'/lang/'.$language->getName().'/website.ini';
            $translator->translations->put($language, TranslationsLoader::load($filename));
        }

        return $translator;
    }
}
