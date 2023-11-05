<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigExtension;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

#[Singleton(collection: SingletonCollection::TwigExtension)]
#[TwigExtension]
readonly class TwigBridgeExtension implements ExtensionInterface
{
    public function __construct(
        private TwigFilterIntlFormatDate $filterIntlFormatDate,
        private TwigFilterTrans $filterTrans,
        private TwigFunctionCSPNonce $functionCspNonce,
        private TwigFunctionCSRFToken $functionCSRFToken,
        private TwigFunctionGatekeeperCan $functionGatekeeperCan,
        private TwigFunctionGatekeeperCanCrud $functionGatekeeperCanCrud,
        private TwigFunctionOld $functionOld,
        private TwigFunctionRoute $functionRoute,
    ) {}

    public function getFilters()
    {
        $safe = [
            'is_safe' => ['html'],
        ];

        return [
            new TwigFilter('intl_format_date', $this->filterIntlFormatDate, $safe),
            new TwigFilter('trans', $this->filterTrans, $safe),
        ];
    }

    public function getFunctions()
    {
        $safe = [
            'is_safe' => ['html'],
        ];

        return [
            new TwigFunction('can', $this->functionGatekeeperCan, $safe),
            new TwigFunction('can_crud', $this->functionGatekeeperCanCrud, $safe),
            new TwigFunction('csp_nonce', $this->functionCspNonce, $safe),
            new TwigFunction('csrf_token', $this->functionCSRFToken, $safe),
            new TwigFunction('old', $this->functionOld),
            new TwigFunction('route', $this->functionRoute, $safe),
        ];
    }

    public function getNodeVisitors()
    {
        return [];
    }

    public function getOperators()
    {
        return [
            [],
            [],
        ];
    }

    public function getTests()
    {
        return [];
    }

    public function getTokenParsers()
    {
        return [];
    }
}
