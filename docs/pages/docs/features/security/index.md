---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: Security
description: >
    Learn about authentication, authorization, and use HTTP security headers.
---

# Security

## Overview

**Authentication** is up to your application, but Resonance offers http 
Session Management (alongside the Session Authentication) is compatible with 
Swoole (based on Redis).

**Authorization** is handled by using the internal `Gatekeeper` library. It
offers resource access control based on PHP gates (sort of firewall rules) you
can code in your application.

## Enforcement of Security Rules

In some crucial places, the framework enforces the use of authorization gates. 
For example, {{docs/features/http/controllers}} always check if the user can 
read or modify the model referenced in the URL.

That means sometimes you must provide an authorization gate (and possibly an 
authentication mechanism - unless you want to *explicitly* enable guest access 
to all resources).

{{docs/features/security/*!docs/features/security/index}}
