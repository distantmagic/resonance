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
        <ol class="homepage__features">
            <li class="homepage__feature">
                Optimized for long-running PHP processes. 
            </li>
            <li class="homepage__feature">
                Unique Dependency Injection container that takes what is best from the singleton pattern while mitigating its common issues. Minimzes memory footprint and eases on the garbage collector. 
            </li>
            <li class="homepage__feature">
                Built-in HTTP and WebSocket server built on top of Swoole.
            </li>
            <li class="homepage__feature">
                Build asynchronous GraphQL API with reusable SQL queries. 
            </li>
            <li class="homepage__feature">
                Uses PHP Attributes to configure application services. Use just one configuration file for your application.
            </li>
            <li class="homepage__feature">
                Promise-like objects in PHP implemented as Swoole Futures.
            </li>
            <li class="homepage__feature">
                HTTP Controllers redesigned for asynchronous PHP with additional features to use the entire Swoole's potential.
            </li>
            <li class="homepage__feature">
                Asynchronous event dispatcher based on coroutines.
            </li>
            <li class="homepage__feature">
                PHP Static Site Generator integrated with the framework.
            </li>
            <li class="homepage__feature">
                Deep integration with esbuild assets bundler.
            </li>
        </ol>
    </div>
</div>
