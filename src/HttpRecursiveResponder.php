<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponder\Error\Forbidden;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton]
readonly class HttpRecursiveResponder
{
    public function __construct(
        private BadRequest $badRequest,
        private CSRFManager $csrfManager,
        private CSRFResponderAggregate $csrfResponderAggregate,
        private Forbidden $forbidden,
        private Gatekeeper $gatekeeper,
        private SiteActionSubjectAggregate $siteActionSubjectAggregate,
    ) {}

    public function respondRecursive(Request $request, Response $response, ?HttpResponderInterface $responder): void
    {
        while ($responder instanceof HttpResponderInterface) {
            $responder = $this->authorizeSiteAction($request, $responder);
            $responder = $this->validateCSRFToken($request, $responder);

            $responder = $responder->respond($request, $response);
        }
    }

    private function authorizeSiteAction(Request $request, HttpResponderInterface $responder): HttpResponderInterface
    {
        $siteActions = $this->siteActionSubjectAggregate->getSiteActions($responder);

        if ($siteActions->isEmpty()) {
            return $responder;
        }

        $gatekeeperUserContext = $this->gatekeeper->withRequest($request);

        foreach ($siteActions as $siteAction) {
            if (!$gatekeeperUserContext->can($siteAction)) {
                return $this->forbidden;
            }
        }

        return $responder;
    }

    private function validateCSRFToken(Request $request, HttpResponderInterface $responder): HttpResponderInterface
    {
        if (!$this->csrfResponderAggregate->httpResponders->hasKey($responder)) {
            return $responder;
        }

        $requestDataSource = $this->csrfResponderAggregate->httpResponders->get($responder);
        $requestData = $request->{$requestDataSource->value};

        if (!is_array($requestData) || !$this->csrfManager->checkToken($request, $requestData)) {
            return $this->badRequest;
        }

        return $responder;
    }
}
