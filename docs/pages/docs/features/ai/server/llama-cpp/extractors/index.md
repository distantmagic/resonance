---
collections: 
    - documents
layout: dm:document
parent: docs/features/ai/server/llama-cpp/index
title: Extractors
description: >
    Extract strictly formatted data from user's fuzzy input.
---

# Extractors

:::note
All extractors are tested with [Mistral Instruct 7B](https://mistral.ai/).

If you use a different LLM, especially with much lower number of parameters
like [Phi-2](https://huggingface.co/microsoft/phi-2) you might get 
inconsistent results.
:::

Extractors either map user's input into a strictly formatted data or extract
specific data from user's input.

{{docs/features/ai/server/llama-cpp/extractors/*/index}}
