<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\ProvidesOAuth2Grant;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\OAuth2Grant;
use Distantmagic\Resonance\OAuth2GrantCollection;
use Distantmagic\Resonance\OAuth2GrantProviderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use League\OAuth2\Server\AuthorizationServer;

/**
 * @template-extends SingletonProvider<AuthorizationServer>
 */
#[GrantsFeature(Feature::OAuth2)]
#[RequiresSingletonCollection(SingletonCollection::OAuth2Grant)]
#[Singleton(provides: OAuth2GrantCollection::class)]
final readonly class OAuth2GrantCollectionProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OAuth2GrantCollection
    {
        $oAuth2GrantCollection = new OAuth2GrantCollection();

        foreach ($this->collectGrants($singletons) as $grantAttribute) {
            $oAuth2GrantCollection->oAuth2Grants->add(new OAuth2Grant(
                $grantAttribute->singleton->provideGrant(),
                $grantAttribute->singleton->getAccessTokenTTL(),
            ));
        }

        return $oAuth2GrantCollection;
    }

    /**
     * @return iterable<SingletonAttribute<OAuth2GrantProviderInterface,ProvidesOAuth2Grant>>
     */
    private function collectGrants(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            OAuth2GrantProviderInterface::class,
            ProvidesOAuth2Grant::class,
        );
    }
}
