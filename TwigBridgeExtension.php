<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

#[Singleton]
readonly class TwigBridgeExtension implements ExtensionInterface
{
    public function __construct(
        private TwigFilterIntlFormatDate $filterIntlFormatDate,
        private TwigFilterTrans $filterTrans,
        private TwigFunctionCSPNonce $functionCspNonce,
        private TwigFunctionCSRFToken $functionCSRFToken,
        private TwigFunctionEsbuild $functionEsbuild,
        private TwigFunctionEsbuildPreloads $functionEsbuildPreloads,
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
            new TwigFunction('csrf_token', $this->functionCSRFToken, $safe),
            new TwigFunction('csp_nonce', $this->functionCspNonce, $safe),
            new TwigFunction('esbuild', $this->functionEsbuild, $safe),
            new TwigFunction('esbuild_preloads', $this->functionEsbuildPreloads, $safe),
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
