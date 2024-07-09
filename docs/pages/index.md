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
    PHP Framework designed from the ground up to facilitate 
    interoperability and messaging between services in your infrastructure and
    beyond.
---

<div class="homepage homepage--title">
    <div class="homepage__content">
        <hgroup class="homepage__title">
            <h1>PHP Framework for Next-Gen Web Apps</h1>
            <h2>
                Build Copilots, Conversational Applications, IO Intensive 
                Services, and more.
            </h2>
            <p>
                Resonance is designed to build IO-intensive web applications 
                from the ground up, making the development and maintenance as 
                predictable and easy as possible.            
            </p>
            <p>
                It provides AI capabilities through llama.cpp and integration 
                with Open Source foundational LLMs, which makes it a great fit 
                for building conversational applications and copilots.
            </p>
            <p>
                Takes full advantage of asynchronous PHP.
            </p>
            <a 
                class="homepage__cta"
                href="/docs/features/"
            >
                Get Started
            </a>
        </hgroup>
    </div>
</div>
<div class="homepage-gallery homepage-gallery--reasons">
    <h3>Why Resonance?</h3>
    <ul class="homepage-gallery__grid">
        <li class="homepage-gallery__grid-item">
            <h4>
                Predictable Performance
            </h4>
            <p>
                Resonance is designed with a few priorities: no memory leaks, blocking operations, and garbage collector surprises.
            </p>
            <p>
                Most of the internals are read-only and stateless. After the application startup, nothing disturbs JIT and opcode (Reflection is only used during the application startup), so there are no surprise slowdowns during runtime.
            </p>
            <p>
                Dependency Injection container is designed to prevent any cyclical dependencies between services.
            </p>
        </li>
        <li class="homepage-gallery__grid-item">
            <h4>
                Opinionated
            </h4>
            <p>
                All the libraries under the hood have been thoroughly tested to ensure they work together correctly, complement each other, and work perfectly under async environments.
            </p>
            <p>
                For example, Resonance implements custom <a href="https://www.doctrine-project.org/">Doctrine</a> drivers, so it uses Swoole's connection pools.
            </p>
        </li>
        <li class="homepage-gallery__grid-item">
            <h4>
                Resolves Input/Output Issues
            </h4>
            <p>
                Resonance is designed to handle IO-intensive tasks, such as serving Machine Learning models, handling WebSocket connections, and processing long-running HTTP requests.
            </p>
            <p>
                It views modern applications as a mix of services that communicate with each other asynchronously, including AI completions and ML inferences, so it provides a set of tools to make this communication as easy as possible.
            </p>
        </li>
        <li class="homepage-gallery__grid-item">
            <h4>
                Complete Package
            </h4>
            <p>
                Resonance includes everything you need to build a modern web application, from the HTTP server to the AI capabilities.
            </p>
            <p>
                It provides security features, HTML templating, integration with open-source LLMs, and provides capability to serve ML models.
            </p>
        </li>
    </ul>
</div>
<div class="homepage-gallery homepage-gallery--releases">
    <h3>New Releases</h3>
    <ul class="homepage-gallery__items">
        <li class="homepage-gallery__item">
            <h4>
                <a href="/docs/features/ai/machine-learning/">
                    Serve Machine Learning Models
                    <span class="homepage-gallery__version">v0.31.0</span>
                </a>
            </h4>
            <p>
                Resonance integrates with Rubix ML to serve Machine Learning
                models in the same codebase as the rest of your application.
            </p>
            <a
                class="homepage-gallery__item__learnmore"
                href="/docs/features/ai/machine-learning/"
            >Learn More</a>
        </li>
        <li class="homepage-gallery__item">
            <h4>
                <a href="/tutorials/semi-scripted-conversational-applications/">
                    Semi-Scripted Conversational Applications
                    <span class="homepage-gallery__version">tutorial</span>
                </a>
            </h4>
            <p>
                Large Language Models, when used as a user interface, enable 
                users to create advanced applications that can replace most 
                traditional interfaces.
            </p>
            <a 
                class="homepage-gallery__item__learnmore"
                href="/tutorials/semi-scripted-conversational-applications/"
            >Learn More</a>
        </li>
        <li class="homepage-gallery__item">
            <h4>
                <a href="/docs/features/conversational-applications/dialogue-nodes/">
                    Dialogue Nodes
                    <span class="homepage-gallery__version">v0.30.0</span>
                </a>
            </h4>
            <p>
                Build and maintain copilots, scripted dialogues, semi-scripted
                dialogues and fully conversational applications.
            </p>
            <a 
                class="homepage-gallery__item__learnmore"
                href="/docs/features/conversational-applications/dialogue-nodes/"
            >Learn More</a>
        </li>
        <li class="homepage-gallery__item">
            <h4>
                <a href="/docs/features/ai/server/llama-cpp/extractors/">
                    Extract Data with AI
                    <span class="homepage-gallery__version">v0.30.0</span>
                </a>
            </h4>
            <iframe 
                src="https://www.youtube.com/embed/Jz-p6Bhcm54" 
                title="YouTube video player" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                referrerpolicy="strict-origin-when-cross-origin" 
                allowfullscreen
            ></iframe>
        </li>
        <li class="homepage-gallery__item">
            <h4>
                <a href="/docs/features/http/responders.html">
                    Function Responders
                    <span class="homepage-gallery__version">v0.29.0</span>
                </a>
            </h4>
            <p>
                You can use functions as responders to handle incoming HTTP 
                requests.
            </p>
            <a 
                class="homepage-gallery__item__learnmore"
                href="/docs/features/http/responders.html"
            >Learn More</a>
        </li>
        <li class="homepage-gallery__item">
            <h4>
                <a href="/docs/features/observability/observable-task-table/">
                    Observable Tasks
                    <span class="homepage-gallery__version">v0.28.0</span>
                </a>
            </h4>
            <iframe 
                src="https://www.youtube.com/embed/Ac5Ww4PBPlY?si=-1JDugBt3BmIE-GG" 
                title="YouTube video player" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                referrerpolicy="strict-origin-when-cross-origin" 
                allowfullscreen
            ></iframe>
        </li>
        <li class="homepage-gallery__item">
            <h4>
                <a href="/docs/features/grpc/">
                    gRPC
                    <span class="homepage-gallery__version">v0.24.0</span>
                </a>
            </h4>
            <p>
                Use gRPC to communicate between services.
            </p>
            <a 
                class="homepage-gallery__item__learnmore"
                href="/docs/features/grpc/"
            >Learn More</a>
        </li>
        <li class="homepage-gallery__item">
            <h4>
                <a href="/docs/features/validation/constraints/">
                    Constraints Schema
                    <span class="homepage-gallery__version">v0.23.0</span>
                </a>
            </h4>
            <p>
                Use constraints to validate incoming data.
            </p>
            <a 
                class="homepage-gallery__item__learnmore"
                href="/docs/features/validation/constraints/"
            >Learn More</a>
        </li>
        <li class="homepage-gallery__item">
            <h4>
                <a href="/docs/features/ai/prompt-subject-responders/">
                    Prompt Subject Responders
                    <span class="homepage-gallery__version">v0.20.0</span>
                </a>
            </h4>
            <iframe 
                src="https://www.youtube.com/embed/pCyEBueNw24" 
                title="YouTube video player" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                referrerpolicy="strict-origin-when-cross-origin" 
                allowfullscreen
            ></iframe>
        </li>
        <li class="homepage-gallery__item">
            <h4>
                <a href="/docs/features/swoole-server-tasks/">
                    Swoole Server Tasks
                    <span class="homepage-gallery__version">v0.18.0</span>
                </a>
            </h4>
            <p>
                Use Swoole tasks to handle long running tasks.
            </p>
            <a 
                class="homepage-gallery__item__learnmore"
                href="/docs/features/swoole-server-tasks/"
            >Learn More</a>
        </li>
        <li class="homepage-gallery__item">
            <h4>
                <a href="/docs/features/security/oauth2/">
                    OAuth 2.0
                    <span class="homepage-gallery__version">v0.10.0</span>
                </a>
            </h4>
            <p>
                Use OAuth 2.0 to authenticate users.
            </p>
            <a 
                class="homepage-gallery__item__learnmore"
                href="/docs/features/security/oauth2/"
            >Learn More</a>
        </li>
    </ul>
</div>
<div class="homepage homepage--features">
    <div class="homepage__content">
        <ul class="homepage__examples">
            <li class="formatted-content homepage__example">
                <h2 class="homepage__example__title">
                    Simple Things Remain Simple
                </h2>
                <div class="homepage__example__description">
                    <p>
                        Writing HTTP controllers is similar to how it's done in 
                        the synchronous code.
                    </p>
                    <p>
                        Controllers have new exciting features that take 
                        advantage of the asynchronous environment.
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
)]
function Homepage(ServerRequestInterface $request, ResponseInterface $response): TwigTemplate
{
    return new TwigTemplate('website/homepage.twig');
}</code></pre>
            </li>
            <li class="formatted-content homepage__example">
                <h2 class="homepage__example__title">
                    Chat with Open-Source LLMs
                </h2>
                <div class="homepage__example__description">
                    <p>
                        Create prompt controllers to directly answer user's 
                        prompts.
                    </p>
                    <p>
                        LLM takes care of determining user's intention, you
                        can focus on taking an appropriate action.
                    </p>
                    <a 
                        class="homepage__cta homepage__cta--example"
                        href="/docs/features/ai/"
                    >
                        Learn More
                    </a>
                </div>
                <pre class="homepage__example__code fenced-code"><code 
                        class="language-php"
                        data-controller="hljs"
                        data-hljs-language-value="php"
                    >#[RespondsToPromptSubject(
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
}</code></pre>
            </li>
            <li class="formatted-content homepage__example">
                <h2 class="homepage__example__title">
                    Serve Machine Learning Models
                </h2>
                <div class="homepage__example__description">
                    <p>
                        Resonance integrates with
                        <a href="https://rubixml.com/" target="_blank">Rubix ML</a>
                        and allows you to serve Machine Learning models in the
                        same codebase as the rest of your application.
                    </p>
                    <p>
                        Resonance allows you to serve inferences from your
                        models through HTTP, WebSocket, and other protocols.
                    </p>
                    <a
                        class="homepage__cta homepage__cta--example"
                        href="/docs/features/ai/"
                    >
                        Learn More
                    </a>
                </div>
                <pre class="homepage__example__code fenced-code"><code
                        class="language-php"
                        data-controller="hljs"
                        data-hljs-language-value="php"
                    >#[RespondsToHttp(
    method: RequestMethod::POST,
    pattern: '/predict',
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
readonly class Predict extends HttpResponder
{
    private Estimator $model;

    public function __construct()
    {
        $this->model = PersistentModel::load(new Filesystem(DM_ROOT.'/models/iris.model'));
    }

    public function respond(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): HttpInterceptableInterface
    {
        $dataset = new Unlabeled($request->getParsedBody());

        $predictions = $this->model->predict($dataset);

        return new JsonResponse($predictions);
    }
}</code></pre>
            </li>
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
                        href="/docs/features/"
                    >
                        Learn More
                    </a>
                </div>
                <pre class="homepage__example__code fenced-code"><code 
                        class="language-php"
                        data-controller="hljs"
                        data-hljs-language-value="php"
                    >#[RespondsToWebSocketJsonRPC(JsonRPCMethod::Echo)]
#[Singleton(collection: SingletonCollection::WebSocketJsonRPCResponder)]
final readonly class EchoResponder extends WebSocketJsonRPCResponder
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
        $webSocketConnection->push(new RPCResponse(
            $rpcRequest,
            $rpcRequest->payload,
        ));
    }
}</code></pre>
            </li>
            <li class="formatted-content homepage__example">
                <h2 class="homepage__example__title">
                    Consistency is Key
                </h2>
                <div class="homepage__example__description">
                    <p>
                        You can keep the same approach to writing software 
                        no matter the size of your project.
                    </p>
                    <p>
                        There are no growing central configuration files 
                        or service dependencies registries. Every relation 
                        between code modules is local to those modules.
                    </p>
                    <a 
                        class="homepage__cta homepage__cta--example"
                        href="/docs/features/events/"
                    >
                        Learn More
                    </a>
                </div>
                <pre class="homepage__example__code fenced-code"><code 
                        class="language-php"
                        data-controller="hljs"
                        data-hljs-language-value="php"
                    >#[ListensTo(HttpServerStarted::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class InitializeErrorReporting extends EventListener
{
    public function handle(object $event): void
    {
        // ...
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
                        There is no need for an elaborate configuration of 
                        services. Everything is handled by the attributes.
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
                        optimizing the resources' usage.
                    </p>
                    <p>
                        All fields can be resolved asynchronously.
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
}</code></pre>
            </li>
        </ul>
    </div>
</div>
