<?php

declare(strict_types=1);

namespace Resonance\Template\Layout\Json;

use Resonance\Attribute\Singleton;
use Resonance\HttpError;
use Resonance\Template\ErrorTemplateInterface;
use Resonance\Template\Layout\Json;
use Swoole\Http\Request;
use Swoole\Http\Response;
use WeakMap;

#[Singleton]
readonly class Error extends Json implements ErrorTemplateInterface
{
    /**
     * @var WeakMap<Request,HttpError> $responseData
     */
    private WeakMap $errors;

    public function __construct()
    {
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
