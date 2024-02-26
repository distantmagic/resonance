<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton]
final readonly class SecurityPolicyHeaders
{
    public function __construct(
        private ContentSecurityPolicyRulesRepository $contentSecurityPolicyRulesRepository,
        private CSPNonceManager $cspNonceManager,
    ) {}

    public function sendAssetHeaders(ResponseInterface $response): ResponseInterface
    {
        return $this->sendCrossOriginPolicies(
            $this->sendRefererPolicies(
                $this->sendXPolicies($response)
            )
        )
            ->withHeader('cache-control', 'public, max-age=31536000, immutable')
            ->withHeader('service-worker-allowed', '/')
            ->withHeader('x-service-worker-cache', 'true')
        ;
    }

    public function sendContentSecurityPolicyHeader(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $contentSecurityPolicyRequestRules = $this->contentSecurityPolicyRulesRepository->from($request);

        return $response->withHeader('content-security-policy', implode(';', [
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

    public function sendJsonPagePolicyHeaders(ResponseInterface $response): ResponseInterface
    {
        return $this->sendRefererPolicies(
            $this->sendServer(
                $this->sendXPolicies($response)
            )
        );
    }

    public function sendTemplatedPagePolicyHeaders(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->sendContentSecurityPolicyHeader(
            $request,
            $this->sendCrossOriginPolicies(
                $this->sendRefererPolicies(
                    $this->sendServer(
                        $this->sendXPolicies($response)
                    )
                )
            ),
        );
    }

    private function getHeaderNonce(ServerRequestInterface $request): string
    {
        $nonce = $this->cspNonceManager->getRequestNonce($request);

        return "'nonce-".$nonce."'";
    }

    private function sendCrossOriginPolicies(ResponseInterface $response): ResponseInterface
    {

        return $response
            ->withHeader('cross-origin-embedder-policy', 'require-corp')
            ->withHeader('cross-origin-opener-policy', 'same-origin')
        ;
    }

    private function sendRefererPolicies(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('x-referrer-policy', 'no-referrer');
    }

    private function sendServer(ResponseInterface $response): ResponseInterface
    {
        // https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html#server
        // Remove this header or set non-informative values.
        return $response->withHeader('server', 'server');
    }

    private function sendXPolicies(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader('x-content-type-options', 'nosniff')
            ->withHeader('x-frame-options', 'deny')
            ->withHeader('x-permitted-cross-domain-policies', 'deny')
        ;
    }
}
