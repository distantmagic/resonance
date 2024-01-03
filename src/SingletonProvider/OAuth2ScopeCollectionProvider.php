<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ProvidesOAuth2Scope;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\OAuth2ScopeCollection;
use Distantmagic\Resonance\OAuth2ScopeInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use LogicException;

/**
 * @template-extends SingletonProvider<OAuth2ScopeCollection>
 */
#[Singleton(
    grantsFeature: Feature::OAuth2,
    provides: OAuth2ScopeCollection::class,
)]
final readonly class OAuth2ScopeCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OAuth2ScopeCollection
    {
        $scopeCollection = new OAuth2ScopeCollection();

        foreach ($phpProjectFiles->findByAttribute(ProvidesOAuth2Scope::class) as $scopeFile) {
            $className = $scopeFile->reflectionClass->getName();

            if (is_a($className, OAuth2ScopeInterface::class, true)) {
                $scopeCollection->addScope($scopeFile->attribute, $className);
            } else {
                throw new LogicException('Scope must be an instance of '.OAuth2ScopeInterface::class);
            }
        }

        return $scopeCollection;
    }
}
