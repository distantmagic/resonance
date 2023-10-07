<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Template\Layout\Json;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpError;
use Distantmagic\Resonance\JsonErrorTemplateInterface;
use Distantmagic\Resonance\SecurityPolicyHeaders;
use Distantmagic\Resonance\Template\Layout\Json;
use Swoole\Http\Request;
use Swoole\Http\Response;
use WeakMap;

#[Singleton(provides: JsonErrorTemplateInterface::class)]
readonly class Error extends Json implements JsonErrorTemplateInterface
{
    /**
     * @var WeakMap<Request,HttpError> $responseData
     */
    private WeakMap $errors;

    public function __construct(SecurityPolicyHeaders $securityPolicyHeaders)
    {
        parent::__construct($securityPolicyHeaders);

        /**
         * @var WeakMap<Request,HttpError>
         */
        $this->errors = new WeakMap();
    }

    public function setError(Request $request, HttpError $httpError): void
    {
        $this->errors->offsetSet($request, $httpError);
    }

    protected function renderJson(Request $request, Response $response): array
    {
        $error = $this->errors->offsetGet($request);

        return [
            'code' => $error->code(),
            'message' => $error->message($request),
        ];
    }
}
