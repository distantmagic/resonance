<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;

/**
 * `swoole_error_log` does not return any value.
 *
 * @psalm-suppress UnusedFunctionCall
 */
#[Singleton(provides: LoggerInterface::class)]
final readonly class SwooleLoggerBridge implements LoggerInterface
{
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->error($message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->error($message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        swoole_error_log(SWOOLE_LOG_DEBUG, (string) $message);
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->error($message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        swoole_error_log(SWOOLE_LOG_ERROR, (string) $message);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        swoole_error_log(SWOOLE_LOG_INFO, (string) $message);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        match ($level) {
            LogLevel::ALERT => $this->alert($message, $context),
            LogLevel::CRITICAL => $this->critical($message, $context),
            LogLevel::DEBUG => $this->debug($message, $context),
            LogLevel::EMERGENCY => $this->emergency($message, $context),
            LogLevel::ERROR => $this->error($message, $context),
            LogLevel::INFO => $this->info($message, $context),
            LogLevel::NOTICE => $this->notice($message, $context),
            LogLevel::WARNING => $this->warning($message, $context),
            default => throw new InvalidArgumentException('Invalid log level: '.(string) $level),
        };
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        swoole_error_log(SWOOLE_LOG_NOTICE, (string) $message);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        swoole_error_log(SWOOLE_LOG_WARNING, (string) $message);
    }
}
