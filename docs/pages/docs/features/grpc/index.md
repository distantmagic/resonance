---
collections:
    - documents
layout: dm:document
parent: docs/features/index
title: gRPC
description: >
    Use gRPC for remote procedure calls and interoperability.
---

# gRPC

# Installation

You can find instruction on the official 
[Google documentation page](https://cloud.google.com/php/grpc).

## PHP Extensions

On Debian-based systems:

```shell
$ sudo apt install autoconf zlib1g-dev php-dev php-pear
$ sudo pecl install grpc
$ sudo pecl install protobuf
```

Enable both `grpc.so` and `protobuf.so` plugins in your php.ini config.

## Protocol Buffers Compiler

```shell
$ sudo apt install protobuf-compiler
```

## GRPC PHP Plugin

You need to clone https://github.com/grpc/grpc repo, then follow their build
instructions from https://github.com/grpc/grpc/blob/v1.61.0/src/php/README.md 

You can use [bazel](https://bazel.build/) to build the plugin.

```shell
$ sudo apt install bazel-bootstrap clang
```

Then, in the folder with `grpc/grpc`:

```shell
$ bazel build @com_google_protobuf//:protoc
$ bazel build src/compiler:grpc_php_plugin
```

# Configuration

```ini file:config.ini
[grpc]
test[grpc_php_plugin_bin] = %DM_ROOT%/grpc_php_plugin
test[out_directory] = %DM_ROOT%/grpc
test[proto_file] = %DM_ROOT%/../protos/hello.proto
test[protoc_bin] = /usr/bin/protoc
test[protos_directory] = %DM_ROOT%/../protos
test[server_host] = 127.0.0.1
test[server_port] = 9503
```

# Generating Code

:::caution
Resonance replaces the default `Grpc\BaseStub` class for clients with 
`Distantmagic\Resonance\GrpcBaseClient`. 

That class user [Hyperf's GRPC client](https://github.com/hyperf/grpc-client)
under the hood.

Resonance also adds a few methods that allow to use the GRPC client as 
singleton and easily reuse it among multiple requests.
:::

First, decide on what namespace your generated files are going to use. In this
example, we will use `App\\Generated`. 

## Update `composer.json`

Adjust your `composer.json` to incorporate both your namespace and 
`GPBMetadata` namespaces:

```json file:composer.json
{
    "autoload": {
        "files": [
            "constants.php"
        ],
        "psr-4": {
            "App\\": "app",
            "App\\Generated\\": "grpc/App/Generated",
            "GPBMetadata\\": "grpc/GPBMetadata"
        }
    }
}
```

Update autoloader to incorporate changes:

```shell
$ composer dump-autoload
```

You can adjust PHP generated namespaces in the protocol buffers file:

```protobuf file:hello.proto
syntax = "proto3";

package helloworld;

option php_namespace = "App\\Generated\\Helloworld";

service Greeter {
  rpc sayHello (HelloRequest) returns (HelloReply) {}

  rpc sayHelloStreamReply (HelloRequest) returns (stream HelloReply) {}

  rpc sayHelloBidiStream (stream HelloRequest) returns (stream HelloReply) {}
}

message HelloRequest {
  string name = 1;
}

message HelloReply {
  string message = 1;
}
```

## Generate PHP Code from Proto

Finally, run `grpc:generate` command:

```shell
$ php ./bin/resonance.php grpc:generate
```

If all is successful, you should see generated client classes in your `grpc`
directory.

## Add Generated Classes to the Container

In your `container.php` file, add generated classess to the 
{{docs/features/dependency-injection/index}} container. That will allow to
use generated clients throught the container:

```php
// (...)

$container = new DependencyInjectionContainer();
$container->phpProjectFiles->indexDirectory(DM_RESONANCE_ROOT);
$container->phpProjectFiles->indexDirectory(DM_APP_ROOT);
$container->phpProjectFiles->indexDirectory(DM_ROOT.'/grpc');
$container->registerSingletons();

return $container;
```

{{docs/features/grpc/*/index}}
