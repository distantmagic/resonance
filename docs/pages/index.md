---
collections: 
    - name: primary_navigation
      next: docs/index
content_type: html
layout: dm:page
register_stylesheets:
    - docs-page-homepage.css
title: Resonance
description: >
    PHP Hub Framework designed from the ground up to facilitate 
    interoperability and messaging between services in your infrastructure and
    beyond.
---

<div class="homepage">
    <div class="homepage__content">
        <hgroup class="homepage__title">
            <h1>Resonance</h1>
            <h2>PHP Hub Framework</h2>
            <p>
                Designed from the ground up to facilitate interoperability
                and messaging between services in your infrastructure and 
                beyond.
            </p>
            <p>
                Takes full advantage of asynchronous PHP.
            </p>
            <p>
                Built on top of Swoole.  
            </p>
            <a 
                class="homepage__cta"
                href="/docs/getting-started/"
            >
                Get Started
            </a>
        </hgroup>
        <ul class="homepage__examples">
            <li class="formatted-content homepage__example">
                <h2 class="homepage__example__title">
                    Asynchronous Where it Matters
                </h2>
                <div class="homepage__example__description">
                    <p>
                        Respond asynchronously to incoming RPC or WebSocket
                        messages (or both combined) with little overhead.
                    </p>
                    <p>
                        You can set up all the asynchronous features using
                        attributes. No elaborate configuration is needed.
                    </p>
                    <a 
                        class="homepage__cta homepage__cta--example"
                        href="/docs/getting-started/"
                    >
                        Learn More
                    </a>
                </div>
                <pre class="homepage__example__code fenced-code"><code 
                        class="language-php"
                        data-controller="hljs"
                        data-hljs-language-value="php"
                    >#[RespondsToWebSocketRPC(RPCMethod::Echo)]
#[Singleton(collection: SingletonCollection::WebSocketRPCResponder)]
final readonly class EchoResponder extends WebSocketRPCResponder
{
    protected function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCRequest $rpcRequest,
    ): void {
        $webSocketConnection->push(new RPCResponse(
            $rpcRequest->requestId,
            (string) $rpcRequest->payload,
        ));
    }
}</code></pre>
            </li>
            <li class="formatted-content homepage__example">
                <h2 class="homepage__example__title">
                    Simple Things Remain Simple
                </h2>
                <div class="homepage__example__description">
                    <p>
                        Writing HTTP controllers is similar to how it's done 
                        in the synchronous code.
                    </p>
                    <p>
                        Controllers have a new exciting features that take 
                        advantage of the asynchronous environment, but you can 
                        add them where they bring value.
                    </p>
                    <a 
                        class="homepage__cta homepage__cta--example"
                        href="/docs/features/http/controllers.html"
                    >
                        Learn More
                    </a>
                </div>
                <pre class="homepage__example__code fenced-code"><code 
                        class="language-php"
                        data-controller="hljs"
                        data-hljs-language-value="php"
                    >#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/',
    routeSymbol: HttpRouteSymbol::Homepage,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
readonly class Homepage implements HttpResponderInterface
{
    public function respond(Request $request, Response $response): TwigTemplate
    {
        return new TwigTemplate('website/homepage.twig');
    }
}</code></pre>
            </li>
            <li class="formatted-content homepage__example">
                <h2 class="homepage__example__title">
                    Powerful Dependency Injection
                </h2>
                <div class="homepage__example__description">
                    <p>
                        Your project's files are indexed when the application
                        starts. Relations between services are then set up
                        by using attributes.
                    </p>
                    <p>
                        There is no need for elaborate services configuration.
                        Everything is handled by the attributes.
                    </p>
                    <a 
                        class="homepage__cta homepage__cta--example"
                        href="/docs/features/dependency-injection/"
                    >
                        Learn More
                    </a>
                </div>
                <pre class="homepage__example__code fenced-code"><code 
                        class="language-php"
                        data-controller="hljs"
                        data-hljs-language-value="php"
                    >#[Singleton(provides: LoggerInterface::class)]
readonly class Logger implements LoggerInterface
{
    public function log($level, string|Stringable $message, array $context = []): void
    {
        // ...
    }
}</code></pre>
            </li>
            <li class="formatted-content homepage__example">
                <h2 class="homepage__example__title">
                    Promises in PHP
                </h2>
                <div class="homepage__example__description">
                    <p>
                        Resonance provides a partial implementation of 
                        Promise/A+ spec to handle various asynchronous tasks.
                    </p>
                    <p>
                    </p>
                    <a 
                        class="homepage__cta homepage__cta--example"
                        href="/docs/features/swoole-futures/"
                    >
                        Learn More
                    </a>
                </div>
                <pre class="homepage__example__code fenced-code"><code 
                        class="language-php"
                        data-controller="hljs"
                        data-hljs-language-value="php"
                    >$future1 = new SwooleFuture(function (int $value) {
    assert($value === 1);

    return $value + 2;
});

$future2 = $future1->then(new SwooleFuture(function (int $value) {
    assert($value === 3);

    return $value + 4;
}));

assert($future2->resolve(1)->result === 7);</code></pre>
            </li>
            <li class="formatted-content homepage__example">
                <h2 class="homepage__example__title">
                    GraphQL Out of the Box
                </h2>
                <div class="homepage__example__description">
                    <p>
                        You can build elaborate GraphQL schemas by using just
                        the PHP attributes.
                    </p>
                    <p>
                        Resonance takes care of reusing SQL queries and 
                        optimizing the resources usage.
                    </p>
                    <p>
                        All fields can be resolved asnynchronously.
                    </p>
                    <a 
                        class="homepage__cta homepage__cta--example"
                        href="/docs/features/graphql/"
                    >
                        Learn More
                    </a>
                </div>
                <pre class="homepage__example__code fenced-code"><code 
                        class="language-php"
                        data-controller="hljs"
                        data-hljs-language-value="php"
                    >#[GraphQLRootField(
    name: 'blogPosts',
    type: GraphQLRootFieldType::Query,
)]
#[Singleton(collection: SingletonCollection::GraphQLRootField)]
final readonly class WorldMap implements GraphQLFieldableInterface
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
}</code></pre>
            </li>
        </ul>
    </div>
</div>
