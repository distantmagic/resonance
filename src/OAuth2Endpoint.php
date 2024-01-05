<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum OAuth2Endpoint
{
    case AccessToken;
    case AuthenticatedPage;
    case Authorization;
    case ClientScopeConsentForm;
    case LoginForm;
}
