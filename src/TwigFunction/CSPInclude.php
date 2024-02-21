<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\ContentSecurityPolicyRulesRepository;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigFunction;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Swoole\Http\Request;

#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class CSPInclude extends TwigFunction
{
    public function __construct(
        private ContentSecurityPolicyRulesRepository $contentSecurityPolicyRulesRepository,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        Request $request,
        string $type,
        string $url,
        bool $return = true,
    ): string {
        $this->logger->debug(sprintf('twig_csp_include("%s", "%s)', $type, $url));

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
