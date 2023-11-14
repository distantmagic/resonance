---
collections: 
    - documents
layout: dm:document
parent: docs/features/http/index
title: PSR HTTP Messages
description: >
    Learn how to convert Swoole server requests to PSR server requests.
---

# PSR HTTP Messages

If you need to convert Swoole's HTTP Request object to it's 
[PSR counterpart](https://www.php-fig.org/psr/psr-7/) you can use the 
converters.

# Usage

## Swoole Request -> PSR Server Request

You should only convert requests if you need to use some third-party library 
that relies on them. Primarily because PSR requests do not provide any 
additional features, it's just for standardization. Conversion between request
formats hinders the performance.

`PsrServerRequestConverter` can/should also be used as a singleton. 

```php
/**
 * @var Distantmagic\Resonance\PsrServerRequestConverter $psrServerRequestRepository 
 * @var Swoole\Http\Request $request 
 * @var Psr\Http\Message\ServerRequestInterface $psrRequest
 */
$psrRequest = $psrServerRequestRepository->convertToServerRequest($request);
```

## PSR Response -> Swoole Response

If you want to respond with PSR response, you need to wrap it in 
`PsrResponder`:

```php
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\HttpResponder\PsrResponder;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

readonly class MyResponder extends HttpResponder
{
    public function respond(Request $request, Response $response): HttpResponderInterface
    {
        // (...) obtain psr response somehow

        /**
         * @var ResponseInterface $psrResponse
         */
        return new PsrResponder($psrResponse);
    }
}
```
