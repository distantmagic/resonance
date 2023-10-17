<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpPreprocessor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\PreprocessesHttpResponder;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\ValidatesCSRFToken;
use Distantmagic\Resonance\CSRFManager;
use Distantmagic\Resonance\HttpPreprocessor;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpPreprocessor<ValidatesCSRFToken>
 */
#[PreprocessesHttpResponder(ValidatesCSRFToken::class)]
#[Singleton(collection: SingletonCollection::HttpPreprocessor)]
readonly class ValidatesCSRFTokenPreprocessor extends HttpPreprocessor
{
    public function __construct(
        private BadRequest $badRequest,
        private CSRFManager $csrfManager,
    ) {}

    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpResponderInterface $next,
    ): HttpResponderInterface {
        $requestData = $request->{$attribute->requestDataSource->value};

        if (!is_array($requestData) || !$this->csrfManager->checkToken($request, $requestData)) {
            return $this->badRequest;
        }

        return $next;
    }
}
