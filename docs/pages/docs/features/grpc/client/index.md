---
collections:
    - documents
layout: dm:document
parent: docs/features/grpc/index
title: Client
description: >
    Learn how to setup gRPC client.
---

# Client

# Usage

After setting up the {{docs/features/grpc/index}} you should be able to use the 
generated client as a {{docs/features/dependency-injection/index}} item:

```php
<?php

use App\Generated\Helloworld\GreeterClient;
use App\Generated\Helloworld\HelloRequest;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\WantsFeature;

#[Singleton]
#[WantsFeature(Feature::GrpcClient)]
readonly class MyClass
{
    public function __construct(
        private GreeterClient $greeterClient,
    ) {
    }

    public function doSomething(): void
    {
        $request = new HelloRequest();
        $request->setName('World!');

        $greeterClient->sayHello($request);
    }
}
```
