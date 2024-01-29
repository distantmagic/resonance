<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;
use RuntimeException;
use Swoole\Http\Response;

class InspectableSwooleResponse extends Response
{
    public string $mockContent = '';

    /**
     * @var array<non-empty-string,non-empty-string>
     */
    public array $mockHeaders = [];

    public int $mockStatus = 200;

    public function end(?string $content = null): bool
    {
        $this->mockContent .= (string) $content;

        return true;
    }

    /**
     * @param non-empty-string                         $key
     * @param array<non-empty-string>|non-empty-string $value
     */
    public function header(string $key, array|string $value, bool $format = true): bool
    {
        if (is_array($value)) {
            throw new LogicException('Array headers are not supported');
        }

        $this->mockHeaders[$key] = $value;

        return true;
    }

    public function mockGetCastedContent(): mixed
    {
        if (!$this->mockIsJson()) {
            return $this->mockContent;
        }

        return json_decode(
            json: $this->mockContent,
            flags: JSON_THROW_ON_ERROR,
        );
    }

    /**
     * @return non-empty-string
     */
    public function mockGetkContentType(): string
    {
        if (!isset($this->mockHeaders['content-type'])) {
            throw new RuntimeException('Response content type is not set');
        }

        return $this->mockHeaders['content-type'];
    }

    public function mockIsJson(): bool
    {
        $contentType = ContentType::tryFrom($this->mockGetkContentType());

        return ContentType::ApplicationJson === $contentType;
    }

    public function status(int $http_code, string $reason = ''): bool
    {
        $this->mockStatus = $http_code;

        return true;
    }

    public function write(string $content): bool
    {
        $this->mockContent .= $content;

        return true;
    }
}
