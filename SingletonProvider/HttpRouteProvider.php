<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\RespondsToHttp;
use Resonance\PHPFileIterator;
use Resonance\PHPFileReflectionClassAttributeIterator;
use Resonance\PHPFileReflectionClassIterator;
use Resonance\SingletonProvider;

/**
 * @template TObject of object
 *
 * @template-extends SingletonProvider<TObject>
 */
abstract readonly class HttpRouteProvider extends SingletonProvider
{
    /**
     * @return PHPFileReflectionClassAttributeIterator<object,RespondsToHttp>
     */
    protected function responderAttributes(): PHPFileReflectionClassAttributeIterator
    {
        $projectPhpFiles = new PHPFileIterator(DM_APP_ROOT);
        $projectPhpReflections = new PHPFileReflectionClassIterator($projectPhpFiles);

        return new PHPFileReflectionClassAttributeIterator($projectPhpReflections, RespondsToHttp::class);
    }
}
