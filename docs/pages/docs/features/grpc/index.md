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

#  Usage
