<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

readonly class CommonMarkAdmonitionExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new CommonMarkAdmonitionBlockStartParser());
    }
}
