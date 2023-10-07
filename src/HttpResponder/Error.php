<?php

declare(strict_types=1);

namespace Resonance\HttpResponder;

use Resonance\ContentType;
use Resonance\ContentTypeResponder;
use Resonance\ErrorHttpResponderDependencies;
use Resonance\HtmlErrorTemplateInterface;
use Resonance\HttpError;
use Resonance\HttpResponder;
use Resonance\HttpResponderInterface;
use Resonance\JsonErrorTemplateInterface;
use Resonance\SecurityPolicyHeaders;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class Error extends HttpResponder
{
    private ContentTypeResponder $contentTypeResponder;
    private HtmlErrorTemplateInterface $htmlTemplate;
    private JsonErrorTemplateInterface $jsonTemplate;
    private NotAcceptable $notAcceptable;
    private SecurityPolicyHeaders $securityPolicyHeaders;

    public function __construct(
        ErrorHttpResponderDependencies $errorHttpResponderDependencies,
        private HttpError $httpError,
    ) {
        $this->htmlTemplate = $errorHttpResponderDependencies->htmlTemplate;
        $this->jsonTemplate = $errorHttpResponderDependencies->jsonTemplate;
        $this->notAcceptable = $errorHttpResponderDependencies->notAcceptable;

        $this->securityPolicyHeaders = $errorHttpResponderDependencies->securityPolicyHeaders;
        $this->contentTypeResponder = new ContentTypeResponder();
        $this->contentTypeResponder->responders->add(ContentType::TextHtml);
        $this->contentTypeResponder->responders->add(ContentType::ApplicationJson);
    }

    public function respond(Request $request, Response $response): HttpResponderInterface
    {
        return match ($this->contentTypeResponder->best($request)) {
            ContentType::ApplicationJson => $this->respondWithJson($request, $response),
            ContentType::TextHtml => $this->respondWithHtml($request, $response),
            default => $this->notAcceptable,
        };
    }

    protected function respondWithJson(Request $request, Response $response): HttpResponderInterface
    {
        $response->status($this->httpError->code());
        $this->securityPolicyHeaders->sendJsonPagePolicyHeaders($response);

        $this->jsonTemplate->setError($request, $this->httpError);

        return $this->jsonTemplate;
    }

    private function respondWithHtml(Request $request, Response $response): HttpResponderInterface
    {
        $response->status($this->httpError->code());
        $this->securityPolicyHeaders->sendTemplatedPagePolicyHeaders($request, $response);

        $this->htmlTemplate->setError($request, $this->httpError);

        return $this->htmlTemplate;
    }
}
