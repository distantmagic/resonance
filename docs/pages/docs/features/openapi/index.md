---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: OpenAPI (Swagger)
description: >
    Learn what OpenAPI is and how to use it with Resonance.
---

# OpenAPI (Swagger)

OpenAPI Specification aims to create:

> (...) a standard, language-agnostic interface to HTTP 
> APIs, which allows both humans and computers to discover and understand the 
> capabilities of the service (...)
>
> [<cite>OpenAPI Specification</cite>](https://swagger.io/specification/)

...which, in practice, is a huge JSON/YAML document that describes your API.

Resonance uses class attributes extensively to describe 
{{docs/features/http/routing}}, 
{{docs/features/security/authentication/index}},
{{docs/features/security/oauth2/index}}, and others. Those features define a 
major portion of what OpenAPI aims to expose. Thanks to the attributes, 
Resonance can infer most of such information directly from the code without 
making annotations specific to OpenAPI.

In most cases, you will only have to annotate HTTP responders with what the
responses consist of - that's the only information Resonance cannot infer from
already existing attributes.

{{docs/features/openapi/*/index}}
