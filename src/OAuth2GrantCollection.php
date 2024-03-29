<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;

readonly class OAuth2GrantCollection
{
    /**
     * @var Set<OAuth2Grant>
     */
    public Set $oAuth2Grants;

    public function __construct()
    {
        $this->oAuth2Grants = new Set();
    }
}
