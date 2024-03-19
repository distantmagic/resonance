<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\ObservableTaskTable;
use Distantmagic\Resonance\TwigTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton]
readonly class ObservableTasksDashboard extends HttpResponder
{
    public function __construct(
        private ObservableTaskTable $observableTaskTable,
    ) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface
    {
        return new TwigTemplate(
            $request,
            $response,
            '@resonance/observable_tasks_dashboard.twig',
            [
                'observableTaskTable' => $this->observableTaskTable,
            ]
        );
    }
}
