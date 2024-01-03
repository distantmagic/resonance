<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum OAuth2Endpoint
{
    case AuthenticatedPage;
    case ClientScopeConsentForm;
    case LoginForm;
}
