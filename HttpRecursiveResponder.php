<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\HttpResponder\Error\BadRequest;
use Resonance\HttpResponder\Error\Forbidden;
use Swoole\Http\Request;
use Swoole\Http\Response;

readonly class HttpRecursiveResponder implements HttpResponderInterface
{
    public function __construct(
        private BadRequest $badRequest,
        private CSRFManager $csrfManager,
        private CSRFResponderAggregate $csrfResponderAggregate,
        private Forbidden $forbidden,
        private Gatekeeper $gatekeeper,
        private HttpResponderInterface $responder,
        private SiteActionSubjectAggregate $siteActionSubjectAggregate,
    ) {}

    /**
     * This response is never going to be used as it's used as a final
     * endpoint, so it's ok that this value is possibly
     *
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    public function respond(Request $request, Response $response): null
    {
        $responder = $this->responder;

        while ($responder instanceof HttpResponderInterface) {
            $responder = $this->authorizeSiteAction($request, $responder);
            $responder = $this->validateCSRFToken($request, $responder);

            $responder = $responder->respond($request, $response);
        }

        return null;
    }

    private function authorizeSiteAction(Request $request, HttpResponderInterface $responder): HttpResponderInterface
    {
        foreach ($this->siteActionSubjectAggregate->getSiteActions($responder) as $siteAction) {
            if (!$this->gatekeeper->withRequest($request)->can($siteAction)) {
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
