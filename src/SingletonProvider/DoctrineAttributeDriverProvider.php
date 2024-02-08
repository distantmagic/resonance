<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineAttributeDriver;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Doctrine\ORM\Mapping\Entity;

/**
 * @template-extends SingletonProvider<DoctrineAttributeDriver>
 */
#[GrantsFeature(Feature::Doctrine)]
#[Singleton(provides: DoctrineAttributeDriver::class)]
final readonly class DoctrineAttributeDriverProvider extends SingletonProvider
{
    /**
     * Ask Doctrine to scan JUST the Entities and nothing more.
     */
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): DoctrineAttributeDriver
    {
        $attributeDriver = new DoctrineAttributeDriver();

        foreach ($phpProjectFiles->findByAttribute(Entity::class) as $phpProjectfile) {
            $attributeDriver->addClassName($phpProjectfile->reflectionClass->getName());
        }

        return $attributeDriver;
    }
}
