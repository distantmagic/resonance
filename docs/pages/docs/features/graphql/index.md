---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: GraphQL
description: >
    Build and expose GraphQL schema with asynchronous features.
---

# GraphQL

GraphQL offers an alternative approach to REST APIs, transforming your 
application into a dynamic graph database that enables precise data retrieval.

This approach thrives on the advantages of the asynchronous PHP, as it 
seamlessly retrieves data from multiple sources (such as databases or
APIs) concurrently.

## Under the Hood

Internally, this framework uses 
[webonyx/graphql-php](https://webonyx.github.io/graphql-php/) library to 
implement its GraphQL capabilities.

Resonance utilizes {{docs/features/swoole-futures/index}} to bridge the 
framework, the external GraphQL library, and Swoole and to enable asynchronous 
resolution of GraphQL fields.

{{docs/features/graphql/*!docs/features/graphql/index}}
