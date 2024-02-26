<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

#[Singleton]
final readonly class SecurityPolicyHeaders
{
    public function __construct(
        private ContentSecurityPolicyRulesRepository $contentSecurityPolicyRulesRepository,
        private CSPNonceManager $cspNonceManager,
    ) {}

    public function sendAssetHeaders(Response $response): void
    {
        $this->sendCrossOriginPolicies($response);
        $this->sendRefererPolicies($response);
        $this->sendXPolicies($response);

        $response->header('cache-control', 'public, max-age=31536000, immutable');
        $response->header('service-worker-allowed', '/');
        $response->header('x-service-worker-cache', 'true');
    }

    public function sendContentSecurityPolicyHeader(ServerRequestInterface $request, Response $response): void
    {
        $contentSecurityPolicyRequestRules = $this->contentSecurityPolicyRulesRepository->from($request);

        $response->header('content-security-policy', implode(';', [
            "default-src 'none'",

            "base-uri 'none'",
            "connect-src 'self'",
            "font-src 'self'",
            'form-action '.((string) $contentSecurityPolicyRequestRules->formAction),
            'frame-src '.((string) $contentSecurityPolicyRequestRules->frameSrc),
            "frame-ancestors 'none'",
            "manifest-src 'self'",
            "img-src 'self'",
            "media-src 'self'",
            "object-src 'none'",
            'script-src '.((string) $contentSecurityPolicyRequestRules->scriptSrc),
            "style-src 'self' ".$this->getHeaderNonce($request),
            "worker-src 'self'",

            'upgrade-insecure-requests',
            'block-all-mixed-content',
        ]));
    }

    public function sendJsonPagePolicyHeaders(Response $response): void
    {
        $this->sendRefererPolicies($response);
        $this->sendServer($response);
        $this->sendXPolicies($response);
    }

    public function sendTemplatedPagePolicyHeaders(ServerRequestInterface $request, Response $response): void
    {
        $this->sendContentSecurityPolicyHeader($request, $response);
        $this->sendCrossOriginPolicies($response);
        $this->sendRefererPolicies($response);
        $this->sendServer($response);
        $this->sendXPolicies($response);
    }

    private function getHeaderNonce(ServerRequestInterface $request): string
    {
        $nonce = $this->cspNonceManager->getRequestNonce($request);

        return "'nonce-".$nonce."'";
    }

    private function sendCrossOriginPolicies(Response $response): void
    {
        $response->header('cross-origin-embedder-policy', 'require-corp');
        $response->header('cross-origin-opener-policy', 'same-origin');
    }

    private function sendRefererPolicies(Response $response): void
    {
        $response->header('referrer-policy', 'no-referrer');
    }

    private function sendServer(Response $response): void
    {
        // https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html#server
        // Remove this header or set non-informative values.
        $response->header('server', 'server');
    }

    private function sendXPolicies(Response $response): void
    {
        $response->header('x-content-type-options', 'nosniff');
        $response->header('x-frame-options', 'deny');
        $response->header('x-permitted-cross-domain-policies', 'deny');
    }
}
