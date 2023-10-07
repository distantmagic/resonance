<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<WebSocketProtocol>
 */
readonly class WebSocketProtocolIterator implements IteratorAggregate
{
    public function __construct(private string $header) {}

    /**
     * @return Generator<WebSocketProtocol>
     */
    public function getIterator(): Generator
    {
        $headerProtocols = explode(',', $this->header);

        foreach ($headerProtocols as $headerProtocol) {
            $protocol = WebSocketProtocol::tryFrom(trim($headerProtocol));

            if ($protocol) {
                yield $protocol;
            }
        }
    }
}
