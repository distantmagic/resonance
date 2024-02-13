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
use Ds\Map;
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
        $translator = new TranslatorBridge(
            $this->languageDetector,
            $this->translatorConfiguration,
        );
        $finder = new Finder();
        $translationFiles = $finder
            ->files()
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCS(true)
            ->in($this->translatorConfiguration->baseDirectory)
        ;

        foreach ($translationFiles as $translationFile) {
            $loadedTranslations = TranslationsLoader::load($translationFile->getPathname());
            $relativePath = $translationFile->getRelativePath();

            /**
             * @var Map<string,string>
             */
            $currentTranslations = $translator->translations->hasKey($relativePath)
                ? $translator->translations->get($relativePath)
                : new Map();

            $currentTranslations->putAll($loadedTranslations);

            $translator->translations->put(
                $relativePath,
                $currentTranslations,
            );
        }

        return $translator;
    }
}
