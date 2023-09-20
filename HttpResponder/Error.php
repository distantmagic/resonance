<?php

declare(strict_types=1);

namespace Resonance\HttpResponder;

use App\Template\Layout\Turbo\Error as HtmlErrorTemplate;
use Resonance\ContentType;
use Resonance\ContentTypeResponder;
use Resonance\ErrorHttpResponderDependencies;
use Resonance\HttpError;
use Resonance\HttpResponder;
use Resonance\SecurityPolicyHeaders;
use Resonance\Template\Layout\Json\Error as JsonErrorTemplate;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class Error extends HttpResponder
{
    private ContentTypeResponder $contentTypeResponder;
    private HtmlErrorTemplate $htmlTemplate;
    private JsonErrorTemplate $jsonTemplate;
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

    public function respond(Request $request, Response $response): void
    {
        match ($this->contentTypeResponder->best($request)) {
            ContentType::ApplicationJson => $this->respondWithJson($request, $response),
            ContentType::TextHtml => $this->respondWithHtml($request, $response),
            default => $this->notAcceptable->respond($request, $response),
        };
    }

    private function respondWithHtml(Request $request, Response $response): void
    {
        $response->status($this->httpError->code());
        $this->securityPolicyHeaders->sendTemplatedPagePolicyHeaders($request, $response);

        $this->htmlTemplate->setError($request, $this->httpError);
        $this->htmlTemplate->write($request, $response);
    }

    private function respondWithJson(Request $request, Response $response): void
    {
        $response->status($this->httpError->code());
        $this->securityPolicyHeaders->sendJsonPagePolicyHeaders($response);

        $this->jsonTemplate->setError($request, $this->httpError);
        $this->jsonTemplate->write($request, $response);
    }
}
