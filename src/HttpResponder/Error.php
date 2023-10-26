<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\ContentType;
use Distantmagic\Resonance\ContentTypeResponder;
use Distantmagic\Resonance\ErrorHttpResponderDependencies;
use Distantmagic\Resonance\HtmlErrorTemplateInterface;
use Distantmagic\Resonance\HttpError;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\JsonErrorTemplateInterface;
use Distantmagic\Resonance\SecurityPolicyHeaders;
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

    public function respond(Request $request, Response $response): HttpInterceptableInterface|HttpResponderInterface
    {
        return match ($this->contentTypeResponder->best($request)) {
            ContentType::ApplicationJson => $this->sendJson($request, $response),
            ContentType::TextHtml => $this->sendHtml($request, $response),
            default => $this->notAcceptable,
        };
    }

    protected function sendJson(Request $request, Response $response): HttpInterceptableInterface|HttpResponderInterface
    {
        $response->status($this->httpError->code());
        $this->securityPolicyHeaders->sendJsonPagePolicyHeaders($response);

        return $this->jsonTemplate->renderHttpError($request, $response, $this->httpError);
    }

    private function sendHtml(Request $request, Response $response): HttpInterceptableInterface|HttpResponderInterface
    {
        $response->status($this->httpError->code());
        $this->securityPolicyHeaders->sendTemplatedPagePolicyHeaders($request, $response);

        return $this->htmlTemplate->renderHttpError($request, $response, $this->httpError);
    }
}
