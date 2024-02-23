---
collections: 
    - name: documents
      next: docs/features/index
description: Changelog
layout: dm:document
parent: docs/index
title: Changelog
---

# Changelog

## v0.26.0

- Feature: {{docs/features/ai/prompt-engineering/gemma/index}} prompt template
- Feature {{docs/features/dependency-injection/index}} side effects
- Feature {{docs/features/templating/twig/index}} `csp_include` function

## v0.24.0

- Feature: {{docs/features/grpc/client/index}} client wrapper

## v0.23.0

- Feature: filename constraint in {{docs/features/validation/constraints/index}}

## v0.22.0

- Change: switch to absolute paths in {{docs/features/configuration/index}}
- Feature: interpolate some constants in {{docs/features/configuration/index}}

## v0.21.0

- Feature: Doctrine {{docs/features/database/doctrine/events}} hooks
- Improvement: OpenAPI reads return types from `Can` 
    {{docs/features/openapi/attributes/index}}

## v0.20.0

- Feature: reworked {{docs/features/http/controllers}} parameter resolution handlers

## v0.19.1

- Fix: some input validators were not cached correctly

## v0.19.0

- Feature: add {{docs/features/validation/constraints/index}}

## v0.18.0

- Feature: add {{docs/features/mail/index}}
- Feature: add {{docs/features/swoole-server-tasks/index}}
- Feature: make event dispatcher ({{docs/features/events/index}}) compatible 
    with [PSR-14](https://www.php-fig.org/psr/psr-14/)

## v0.17.0

- Feature: add {{docs/features/vector-store/sqlite-vss/index}} (integration)

## v0.16.0

- Feature: add {{docs/features/ai/prompt-engineering/index}} (basic prompt templates)

## v0.15.0

- Feature: add {{docs/features/http/pipe-messages}}

## v0.14.0

- Feature: add {{docs/features/ai/server/llama-cpp/index}} to integrate with LLMs 

## v0.11.1

- Fix: translation files were incorrectly loaded

## v0.11.0

- Added translation strings parameters ({{docs/features/translations/index}}) - thanks to [@dulkoss](https://github.com/dulkoss) [#2](https://github.com/distantmagic/resonance/pull/2)

## v0.10.0

- Added {{docs/features/security/oauth2/index}} support.
- Added {{docs/features/http/psr-http-messages}} wrapper.
- Added `EntityManagerWeakReference` to {{docs/features/database/doctrine/index}} integration.
