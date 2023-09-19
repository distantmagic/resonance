<?php

declare(strict_types=1);

namespace Resonance;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @internal
 */
final class AcceptHeaderTest extends TestCase
{
    public function test_request_header_is_parsed(): void
    {
        $rawHeader = implode(',', [
            'text/html',
            '  text/*  ',
            'application/xhtml+xml',
            'application/xml  ;   q=0.9',
            'image/avif',
            'image/webp;q=0.0',
            'image/apng',
            '*/*;q=0.8',
        ]);

        $header = new AcceptHeader($rawHeader);

        self::assertEquals([
            'text/html',
            'text/*',
            'application/xhtml+xml',
            'image/avif',
            'image/apng',
            'application/xml',
            '*/*',
        ], $header->sorted->toArray());
    }
}
