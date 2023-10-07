<?php

declare(strict_types=1);

namespace Resonance;

trait TestsDependencyInectionContainerTrait
{
    private static DependencyInjectionContainer $container;

    public static function setUpBeforeClass(): void
    {
        $container = new DependencyInjectionContainer();
        $container->phpProjectFiles->indexDirectory(DM_RESONANCE_ROOT);
        $container->phpProjectFiles->indexDirectory(DM_APP_ROOT);
        $container->registerSingletons();

        self::$container = $container;
    }

    public static function tearDownAfterClass(): void
    {
        self::$container = null;
    }
}
