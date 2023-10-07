<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
final readonly class TemplateFilters
{
    public function escape(?string $str): string
    {
        if (is_null($str)) {
            return '';
        }

        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE);
    }

    public function old(mixed $requestVars, string $field, ?string $default = null): string
    {
        $requestData = new HttpRequestData($requestVars);

        return $this->escape((string) $requestData->get($field, $default));
    }
}
