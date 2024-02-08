<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\BuildsPDOPoolConnection;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\PDOPoolConnectionBuilderCollection;
use Distantmagic\Resonance\PDOPoolConnectionBuilderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpResponderCollection>
 */
#[RequiresSingletonCollection(SingletonCollection::PDOPoolConnectionBuilder)]
#[Singleton(provides: PDOPoolConnectionBuilderCollection::class)]
final readonly class PDOPoolConnectionBuilderCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): PDOPoolConnectionBuilderCollection
    {
        $pdoPoolConnectionBuilderCollection = new PDOPoolConnectionBuilderCollection();

        foreach ($this->collectBuilders($singletons) as $builderAttribute) {
            $pdoPoolConnectionBuilderCollection->addBuilder(
                $builderAttribute->attribute->name,
                $builderAttribute->singleton
            );
        }

        return $pdoPoolConnectionBuilderCollection;
    }

    /**
     * @return iterable<SingletonAttribute<PDOPoolConnectionBuilderInterface,BuildsPDOPoolConnection>>
     */
    private function collectBuilders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            PDOPoolConnectionBuilderInterface::class,
            BuildsPDOPoolConnection::class,
        );
    }
}
