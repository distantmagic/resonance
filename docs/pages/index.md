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
                class="homepage__title__cta"
                href="/docs/getting-started/"
            >
                Get started
            </a>
        </hgroup>
        <nav class="homepage__features">
            <a 
                class="homepage__feature" 
                href="/docs/features/dependency-injection/"
            >
                <h3 class="homepage__feature__title">
                    Optimized for long-running PHP processes. 
                </h3>
                <div class="homepage__feature__description">
                    Unique Dependency Injection container that takes what is best from the singleton pattern while mitigating its common issues. Minimzes memory footprint and eases on the garbage collector. 
                </div>
            </a>
            <a 
                class="homepage__feature" 
                href="/docs/features/graphql/"
            >
                <h3 class="homepage__feature__title">
                    GraphQL
                </h3>
                <div class="homepage__feature__description">
                    Build asynchronous GraphQL API with reusable SQL queries
                    and PHP attributes.
                </div>
            </a>
            <a 
                class="homepage__feature" 
                href="/docs/features/http/server.html"
            >
                <h3 class="homepage__feature__title">
                    Standalone
                </h3>
                <div class="homepage__feature__description">
                    Built-in HTTP and WebSocket server built on top of Swoole.
                </div>
            </a>
            <a 
                class="homepage__feature" 
                href="/docs/features/configuration/"
            >
                <h3 class="homepage__feature__title">
                    Minimal Configuration
                </h3>
                <div class="homepage__feature__description">
                    Uses PHP Attributes to configure application services. Use 
                    just one configuration file for your application.
                </div>
            </a>
            <a 
                class="homepage__feature" 
                href="/docs/features/swoole-futures/"
            >
                <h3 class="homepage__feature__title">
                    Asynchronous
                </h3>
                <div class="homepage__feature__description">
                    Promise-like objects in PHP implemented as Swoole Futures.
                    Asynchronous event dispatcher based on coroutines.
                </div>
            </a>
            <a 
                class="homepage__feature" 
                href="/docs/features/http/"
            >
                <h3 class="homepage__feature__title">
                    Adjusted for Swoole
                </h3>
                <div class="homepage__feature__description">
                    HTTP Controllers redesigned for asynchronous PHP with 
                    additional features to use the entire Swoole's potential.
                </div>
            </a>
            <a 
                class="homepage__feature" 
                href="/docs/features/generating-static-sites/"
            >
                <h3 class="homepage__feature__title">
                    PHP Static Site Generator
                </h3>
                <div class="homepage__feature__description">
                    Generate static pages for your project or use PHP
                    stack to generate the entire static website.
                </div>
            </a>
            <a 
                class="homepage__feature" 
                href="/docs/features/asset-bundling-esbuild/"
            >
                <h3 class="homepage__feature__title">
                    esbuild integration
                </h3>
                <div class="homepage__feature__description">
                    Deep integration with esbuild assets bundler.
                </div>
            </a>
        </nav>
    </div>
</div>
