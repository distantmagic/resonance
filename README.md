# Resonance

[Documentation](https://resonance.distantmagic.com/)

## About

Designed from the ground up to facilitate interoperability and 
messaging between services in your infrastructure and beyond.

Provides AI capabilities.

Takes full advantage of asynchronous PHP. Built on top of 
Swoole.

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
    return new TwigTemplate($request, $response, 'website/homepage.twig');
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

## License

The Resonance framework is open-sourced software licensed under the 
[MIT license](https://opensource.org/licenses/MIT).
