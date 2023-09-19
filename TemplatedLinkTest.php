<?php

declare(strict_types=1);

namespace Resonance;

use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @coversNothing
 *
 * @internal
 */
final class TemplatedLinkTest extends TestCase
{
    public function test_allowed_empty_parameters_are_detected(): void
    {
        $link = new TemplatedLink('/foo');

        self::assertEquals([], $link->getAllowedParameters());
    }

    public function test_allowed_parameters_are_detected(): void
    {
        $link = new TemplatedLink('/foo/{bar}/{baz}');

        self::assertEquals(['bar', 'baz'], $link->getAllowedParameters());
    }

    public function test_link_is_generated(): void
    {
        $link = new TemplatedLink('/foo/{bar}/{baz}');

        self::assertEquals('/foo/wooz/booz', $link->build([
            'bar' => 'wooz',
            'baz' => 'booz',
        ])->getHref());
    }

    public function test_link_regexp_is_validated(): void
    {
        $link = new TemplatedLink('/foo/{bar}');

        $this->expectException(UnexpectedValueException::class);

        $link->build([
            'bar' => 'a/1/3',
        ]);
    }

    public function test_link_with_regexp_is_built(): void
    {
        $link = new TemplatedLink('/public/{asset:[\.\w\/]+}');

        self::assertEquals('/public/js/foo_bar_123.js', $link->buildHref([
            'asset' => 'js/foo_bar_123.js',
        ]));
    }

    public function test_regexp_is_detected(): void
    {
        $link = new TemplatedLink('/public/{asset:[\.\w\/]+}');

        self::assertEquals(['asset'], $link->getAllowedParameters());
    }
}
