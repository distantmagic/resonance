<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\ContentSecurityPolicyRulesRepository;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigFunction;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class CSPInclude extends TwigFunction
{
    public function __construct(
        private ContentSecurityPolicyRulesRepository $contentSecurityPolicyRulesRepository,
    ) {}

    public function __invoke(
        ServerRequestInterface $request,
        string $type,
        string $url,
        bool $return = true,
    ): string {
        $contentSecurityPolicyRequestRules = $this->contentSecurityPolicyRulesRepository->from($request);

        switch ($type) {
            case 'frame-src':
                $contentSecurityPolicyRequestRules->frameSrc->add($url);

                break;
            case 'script-src':
                $contentSecurityPolicyRequestRules->scriptSrc->add($url);

                break;
            default:
                throw new InvalidArgumentException('Unknown CSP type: '.$type);
        }

        if ($return) {
            return $url;
        }

        return '';
    }

    public function getName(): string
    {
        return 'csp_include';
    }
}
