<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

trait TestsDependencyInectionContainerTrait
{
    private static DependencyInjectionContainer $container;

    public static function setUpBeforeClass(): void
    {
        self::$container = DependencyInjectionContainer::fromGlobals();
    }

    public static function tearDownAfterClass(): void
    {
        self::$container = null;
    }
}
