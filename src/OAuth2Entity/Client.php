<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OAuth2Entity;

use Distantmagic\Resonance\OAuth2Entity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;

class Client extends OAuth2Entity implements ClientEntityInterface
{
    use ClientTrait;

    private $allowPlainTextPkce = false;

    public function isPlainTextPkceAllowed(): bool
    {
        return $this->allowPlainTextPkce;
    }

    public function setAllowPlainTextPkce(bool $allowPlainTextPkce): void
    {
        $this->allowPlainTextPkce = $allowPlainTextPkce;
    }

    public function setConfidential(bool $isConfidential): void
    {
        $this->isConfidential = $isConfidential;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string|string[] $redirectUri
     */
    public function setRedirectUri(array|string $redirectUri): void
    {
        $this->redirectUri = is_string($redirectUri)
            ? [$redirectUri]
            : $redirectUri;
    }
}
