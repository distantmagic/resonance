---
collections: 
    - name: documents
      next: docs/features/http/responders
layout: dm:document
next: docs/features/http/responders
parent: docs/features/http/index
title: Server
description: >
    Configure the Resonance "serve" command and start the http server.
---

# HTTP Server

# Usage

## Setting up the Command

Resonance comes with the `serve` command by default. You can call
`php ./app/resonance.php serve` to start the http server.

You can configure it using the {{docs/features/configuration/index}} file.
If you want to add {{docs/features/websockets/index}}, you can follow their 
configuration page.

You can (and probably should) modify this file alongside the Swoole server 
parameters to fit your project.

```php
<?php

declare(strict_types=1);

namespace App\Commands;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\Event\HttpServerStarted;
use Distantmagic\Resonance\EventDispatcherInterface;
use Distantmagic\Resonance\HttpResponderAggregate;
use Distantmagic\Resonance\SwooleConfiguration;
use Psr\Log\LoggerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Server;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(name: 'serve')]
final class Serve extends Command
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private EventDispatcherInterface $eventDispatcher,
        private HttpResponderAggregate $httpResponderAggregate,
        private LoggerInterface $logger,
        private SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $server = new Server(
            $this->swooleConfiguration->host,
            $this->swooleConfiguration->port,
            SWOOLE_PROCESS,
            SWOOLE_SOCK_TCP | SWOOLE_SSL,
        );

        $server->set([
            'enable_coroutine' => true,
            'enable_deadlock_check' => Environment::Development === $this->applicationConfiguration->environment,
            'log_level' => $this->swooleConfiguration->logLevel,
            'ssl_cert_file' => DM_ROOT.'/'.$this->swooleConfiguration->sslCertFile,
            'ssl_key_file' => DM_ROOT.'/'.$this->swooleConfiguration->sslKeyFile,
            'open_http2_protocol' => true,
        ]);

        $server->on('start', function () {
            $this->eventDispatcher->dispatch(new HttpServerStarted());

            $this->logger->info('HTTP server is listening at https://'.$this->swooleConfiguration->host.':'.$this->swooleConfiguration->port);
        });

        $server->on('message', static function () {});
        $server->on('request', $this->httpResponderAggregate->respond(...));

        return (int) !$server->start();
    }
}
```
