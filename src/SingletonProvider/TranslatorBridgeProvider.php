<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpRequestLanguageDetector;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TranslationsLoader;
use Distantmagic\Resonance\TranslatorBridge;
use Distantmagic\Resonance\TranslatorConfiguration;
use Symfony\Component\Finder\Finder;

/**
 * @template-extends SingletonProvider<TranslatorBridge>
 */
#[Singleton(provides: TranslatorBridge::class)]
final readonly class TranslatorBridgeProvider extends SingletonProvider
{
    public function __construct(
        private HttpRequestLanguageDetector $languageDetector,
        private TranslatorConfiguration $translatorConfiguration,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TranslatorBridge
    {
        $translator = new TranslatorBridge($this->languageDetector);
        $finder = new Finder();
        $translationFiles = $finder
            ->files()
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCS(true)
            ->in(DM_ROOT.'/'.$this->translatorConfiguration->baseDirectory)
        ;

        foreach ($translationFiles as $translationFile) {
            $translator->translations->put(
                $translationFile->getRelativePath(),
                TranslationsLoader::load($translationFile->getPathname())
            );
        }

        return $translator;
    }
}