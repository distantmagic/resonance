---
collections: 
    - name: documents
      next: docs/features/openapi/exposing-schema/index
layout: dm:document
next: docs/features/openapi/exposing-schema/index
parent: docs/features/openapi/index
title: Attributes
description: >
    Learn how to expose OpenAPI schema
---

# Attributes

Resonance can use most attributes you will already use to annotate
your responders. The only attributes specific to OpenAPI implementation 
are `BelongsToOpenAPISchema` and `GivesHttpResponse`.

# Usage

All attributes start with `Distantmagic\Resonance\Attribute\` namespace:

## Class Attributes

Attribute | Description
-|-
`BelongsToOpenAPISchema` (repeatable) | Optionally accepts `schemaSymbol` argument (implements `OpenAPISchemaSymbolInterface`) to denote to which schema the given endpoint belongs. You can repeat it if it belongs to multiple schemas.
`Can` (repeatable) | Used to build [Security Requirement Object](https://swagger.io/specification/#security-requirement-object). See more at: {{docs/features/security/authentication/index}}.
`GivesHttpResponse` (repeatable) | Used to annotate the type of the response. Resonance uses it to build [Response Object](https://swagger.io/specification/#response-object). You need to define one for each status code your responder can return.
`RequiresOAuth2Scope` (repeatable) | Used to build [Security Requirement Object](https://swagger.io/specification/#security-requirement-object). See more at: {{docs/features/security/oauth2/index}}.
`RespondsToHttp` | Used to build [Operation Object](https://swagger.io/specification/#operation-object). See more at: {{docs/features/http/responders}}.

## Controller Parameter Attributes

Attribute | Description
-|-
`DoctrineEntityRouteParameter` | Used to build [Parameter Object](https://swagger.io/specification/#operation-object). See more at: {{docs/features/http/controllers}}.
`SessionAuthenticated` | Used to build [Security Requirement Object](https://swagger.io/specification/#security-requirement-object). See more at: {{docs/features/http/controllers}}.
`ValidatedRequest` | Used to build [Parameter Object](https://swagger.io/specification/#operation-object). See more at: {{docs/features/validation/index}}.
