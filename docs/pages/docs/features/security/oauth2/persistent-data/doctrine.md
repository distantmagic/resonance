---
collections: 
    - documents
layout: dm:document
parent: docs/features/security/oauth2/persistent-data/index
title: Doctrine
description: >
    Learn how to persist OAuth2 tokens and other data by using Doctrine.
---

# Doctrine

If you are already using {{docs/features/database/doctrine/index}}, you might
want to consider this approach instead of using 
{{docs/features/security/oauth2/persistent-data/repositories}}.

# Usage

Instead of implementing several repositories, you can implement just the 
`OAuth2EntityRepositoryInterface` that is compatible with all the grant types.

It's primary purpose is to cast OAuth2 data into your application's Doctrine
entities.

Method | Description
-|-
`convertAccessToken`  | convert internal access token model into your entity
`convertAuthCode`     | convert internal auth code model into your entity
`convertRefreshToken` | convert internal refresh token model into your entity
`findAccessToken`     | convert internal access token model into your entity
`findAuthCode`        | find auth code in your database, return your entity
`findClient`          | find client in your database, return your entity
`findRefreshToken`    | find refresh token in your database, return your entity
`findUser`            | find user in your database, return your entity
`toAccessToken`       | find access token in your database, return your entity
`toClientEntity`      | find client in your database, return your entity
