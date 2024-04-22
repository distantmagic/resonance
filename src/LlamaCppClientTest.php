<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Swoole\Event;

use function Distantmagic\Resonance\helpers\coroutineMustRun;

/**
 * @internal
 */
#[CoversClass(LlamaCppClient::class)]
#[Group('llamacpp')]
final class LlamaCppClientTest extends TestCase
{
    use TestsDependencyInectionContainerTrait;

    protected function tearDown(): void
    {
        Event::wait();
    }

    public function test_completion_is_generated(): void
    {
        $llamaCppClient = self::$container->make(LlamaCppClient::class);

        coroutineMustRun(static function () use ($llamaCppClient) {
            $completion = $llamaCppClient->generateCompletion(new LlamaCppCompletionRequest(
                llmChatHistory: new LlmChatHistory([
                    new LlmChatMessage('user', 'Who are you? Answer in exactly two words.'),
                ]),
            ));

            $ret = '';

            foreach ($completion as $token) {
                $ret .= (string) $token;
            }

            self::assertNotEmpty($ret);
        });
    }

    public function test_health_status_is_checked(): void
    {
        $llamaCppClient = self::$container->make(LlamaCppClient::class);

        self::assertSame(LlamaCppHealthStatus::Ok, $llamaCppClient->getHealth());
    }
}
