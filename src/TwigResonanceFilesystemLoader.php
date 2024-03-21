<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigLoader;
use Twig\Loader\FilesystemLoader;

#[Singleton(collection: SingletonCollection::TwigLoader)]
#[TwigLoader]
class TwigResonanceFilesystemLoader extends FilesystemLoader implements TwigOptionalLoaderInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function beforeRegister(): void
    {
        $this->addPath(DM_RESONANCE_ROOT.'/views', 'resonance');
    }

    public function shouldRegister(): bool
    {
        return is_dir(DM_RESONANCE_ROOT.'/views');
    }
}
