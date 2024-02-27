---
collections: 
    - documents
layout: dm:document
parent: docs/features/security/index
title: Content Security Policy (CSP)
description: >
    Learn how to manage Content Security Policy headers and 
    CSP Nonces.
---

# Content Security Policy (CSP)

> Content Security Policy (CSP) is an added layer of security that helps to 
> detect and mitigate certain types of attacks, including Cross-Site Scripting 
> (XSS) and data injection attacks. These attacks are used for everything from 
> data theft, to site defacement, to malware distribution.
>
> [MDN](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)

Modern browsers implement CSP, but it needs to be activated by sending them a 
series of specific headers.

`Distantmagic\Resonance\SecurityPolicyHeaders` provides not only 
{{docs/features/security/content-security-policy/index}} headers but also some 
additional headers recommended by 
[OWASP's Secure Headers Project](https://owasp.org/www-project-secure-headers/).

# Usage

## Invoking Policies Manually

Some CSP presets are provided by the 
`Distantmagic\Resonance\SecurityPolicyHeaders` class. All of them are 
deliberately restrictive but enable the usage of Nonces through
`Distantmagic\Resonance\CSPNonceManager` to enable the controlled use of the 
inline code.

You can either send them manually by calling the preset methods:

Method | Description
-|-
`sendContentSecurityPolicyHeader` | Sends restrictive CSP headers that prevent embedding all external resources and block all external requests.
`sendTemplatedPagePolicyHeaders` | Sends headers appropriate for a page that uses HTML templates.
`sendJsonPagePolicyHeaders` | Sends headers appropriate for a page with JSON response.

## Invoking Policies Using Attributes

You can use the `#[ContentSecurityPolicy(ContentSecurityPolicyType)]` attribute 
in your {{docs/features/http/responders}} to attach the 
{{docs/features/http/middleware}} that sends CSP headers.

## Using Nonces

### PHP

To use nonces manually, you need to use the CSP Nonce Manager: 

```php
/**
 * @var \Distantmagic\Resonance\CSPNonceManager $cspNonceManager
 * @var \Psr\Http\Message\ServerRequestInterface $request
 */
$cspNonceManager->getRequestNonce($request);
```

The above method returns a `string` with CSP Nonce and also adds this Nonce to
the Content Security Headers returned by the HTTP Response.

### Twig

Twig integration works the same way as PHP Nonce Manager but can be used 
directly in templates without using the `CSPNonceManager` directly.

Learn more at {{docs/features/templating/twig/index}} documentation.
