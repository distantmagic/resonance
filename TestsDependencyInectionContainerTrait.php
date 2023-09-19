<?php

declare(strict_types=1);

namespace Resonance;

trait TestsDependencyInectionContainerTrait
{
    private static DependencyInjectionContainer $container;

    public static function setUpBeforeClass(): void
    {
        $builder = new DependencyInjectionContainerBuilder();

        self::$container = $builder->buildContainer();
    }

    public static function tearDownAfterClass(): void
    {
        self::$container = null;
    }
}
