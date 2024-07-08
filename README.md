# Resonance

## About Resonance

Resonance is designed from the ground up to facilitate interoperability and messaging between services in your infrastructure and beyond. It provides AI capabilities, has a built in web server and integrates with [llama.cpp](https://github.com/ggerganov/llama.cpp).

Takes full advantage of asynchronous PHP. Built on top of Swoole.

## Why Resonance?

### Predictable Performance

Resonance is designed with a few priorities: no memory leaks, blocking operations, and garbage collector surprises.

Most of the internals are read-only and stateless. After the application startup, nothing disturbs JIT and opcode (Reflection is only used during the application startup), so there are no surprise slowdowns during runtime.

### Opinionated

All the libraries under the hood have been thoroughly tested to ensure they work together correctly, complement each other, and work perfectly under async environments.

For example, Resonance implements custom <a href="https://www.doctrine-project.org/">Doctrine</a> drivers, so it uses Swoole's connection pools.

### Resolves Input/Output Issues

Resonance is designed to handle IO-intensive tasks, such as serving Machine Learning models, handling WebSocket connections, and processing long-running HTTP requests.

It views modern applications as a mix of services that communicate with each other asynchronously, including AI completions and ML inferences, so it provides a set of tools to make this communication as easy as possible.

### Complete Package

Resonance includes everything you need to build a modern web application, from the HTTP server to the AI capabilities.

It provides security features, HTML templating, integration with open-source LLMs, and provides capability to serve ML models.

## Documentation

https://resonance.distantmagic.com/

## Installation

It's best to install Resonance by using Composer's create-project command:

```php
composer create-project distantmagic/resonance-project my-project
```

Resonance requires minimum 8.2 version of PHP, as well as Data Structures and Swoole extensions. Read more about required and recommended extensions, as well as other installation methods in our [installation guide](https://resonance.distantmagic.com/docs/getting-started/installation-and-requirements.html).

### First-time use

You'll need to create a `config.ini` file after installing the project (`config.ini.example` is provided) and then use `bin/resonance.php` as an entry point.

### Running the server

`php bin/resonance.php` serve starts the built-in HTTP server. If you need to, you can generate the [SSL Certificate for Local Development](https://resonance.distantmagic.com/docs/extras/ssl-certificate-for-local-development/).

## Features

### Chat with Open-Source LLMs

Create prompt controllers to directly answer user's prompts.

LLM takes care of determining user's intention, you can focus on taking an 
appropriate action.

```php
#[RespondsToPromptSubject(
    action: 'adopt',
    subject: 'cat',
)]
#[Singleton(collection: SingletonCollection::PromptSubjectResponder)]
readonly class CatAdopt implements PromptSubjectResponderInterface
{
    public function respondToPromptSubject(PromptSubjectRequest $request, PromptSubjectResponse $response): void
    {
        // Pipes message through WebSocket... 

        $response->write("Here you go:\n\n");
        $response->write("   |\_._/|\n");
        $response->write("   | o o |\n");
        $response->write("   (  T  )\n");
        $response->write("  .^`-^-`^.\n");
        $response->write("  `.  ;  .`\n");
        $response->write("  | | | | |\n");
        $response->write(" ((_((|))_))\n");
        $response->end();
    }
}
```

### Asynchronous Where it Matters

Respond asynchronously to incoming RPC or WebSocket
messages (or both combined) with little overhead.

You can set up all the asynchronous features using
attributes. No elaborate configuration is needed.

```php
#[RespondsToWebSocketJsonRPC(JsonRPCMethod::Echo)]
#[Singleton(collection: SingletonCollection::WebSocketJsonRPCResponder)]
final readonly class EchoResponder extends WebSocketJsonJsonRPCResponder
{
    public function getConstraint(): Constraint
    {
        return new StringConstraint();
    }

    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCRequest $rpcRequest,
    ): void {
        $webSocketConnection->push(new JsonRPCResponse(
            $rpcRequest,
            $rpcRequest->payload,
        ));
    }
}
```

### Simple Things Remain Simple

Writing HTTP controllers is similar to how it's done in 
the synchronous code.

Controllers have new exciting features that take 
advantage of the asynchronous environment.

```php
#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/',
)]
function Homepage(ServerRequestInterface $request, ResponseInterface $response): TwigTemplate
{
    return new TwigTemplate('website/homepage.twig');
}
```

### Consistency is Key

You can keep the same approach to writing software 
no matter the size of your project.

There are no growing central configuration files 
or service dependencies registries. Every relation 
between code modules is local to those modules.

```php
#[ListensTo(HttpServerStarted::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class InitializeErrorReporting extends EventListener
{
    public function handle(object $event): void
    {
        // ...
    }
}
```

### Promises in PHP

Resonance provides a partial implementation of 
Promise/A+ spec to handle various asynchronous tasks.

```php
$future1 = new SwooleFuture(function (int $value) {
    assert($value === 1);

    return $value + 2;
});

$future2 = $future1->then(new SwooleFuture(function (int $value) {
    assert($value === 3);

    return $value + 4;
}));

assert($future2->resolve(1)->result === 7);
```

### GraphQL Out of the Box

You can build elaborate GraphQL schemas by using just 
the PHP attributes.

Resonance takes care of reusing SQL queries and 
optimizing the resources' usage.

All fields can be resolved asynchronously.

```php
#[GraphQLRootField(
    name: 'blogPosts',
    type: GraphQLRootFieldType::Query,
)]
#[Singleton(collection: SingletonCollection::GraphQLRootField)]
final readonly class Blog implements GraphQLFieldableInterface
{
    public function __construct(
        private DatabaseConnectionPoolRepository $connectionPool,
        private BlogPostType $blogPostType,
    ) {}

    public function resolve(): GraphQLReusableDatabaseQueryInterface
    {
        return new SelectBlogPosts($this->connectionPool);
    }

    public function toGraphQLField(): array
    {
        return [
            'type' => new ListOfType($this->blogPostType),
            'resolve' => $this->resolve(...),
        ];
    }
}
```

## Tutorials

* ['Hello, World' with Resonance](https://resonance.distantmagic.com/tutorials/hello-world/)
* [Session-Based Authentication](https://resonance.distantmagic.com/tutorials/session-based-authentication/)
* [Building a Basic GraphQL Schema](https://resonance.distantmagic.com/tutorials/basic-graphql-schema/)
* [How to Serve LLM Completions (With llama.cpp)?](https://resonance.distantmagic.com/tutorials/how-to-serve-llm-completions/)
* [How to Create LLM WebSocket Chat with llama.cpp?](https://resonance.distantmagic.com/tutorials/how-to-create-llm-websocket-chat-with-llama-cpp/)
* [Semi-Scripted Conversational Applications](https://resonance.distantmagic.com/tutorials/semi-scripted-conversational-applications/)

## Community

You can find official channels here:

- [Discord](https://discord.gg/kysUzFqSCK)
- [Telegram](https://t.me/+AQiDhKlsNBRjMmU0)
  
## License

The Resonance framework is open-sourced software licensed under the 
[MIT license](https://opensource.org/licenses/MIT).
