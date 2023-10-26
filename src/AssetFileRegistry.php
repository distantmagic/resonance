<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder\Error\PageNotFound;
use Ds\Map;
use Swoole\Http\Response;
use Symfony\Component\Finder\Finder;

/**
 * When making any modifications to this class, it's important to be cautious
 * about the potential for Path Traversal attacks.
 */
#[Singleton]
readonly class AssetFileRegistry
{
    /**
     * @var Map<string, string>
     */
    private Map $files;

    public function __construct(
        private PageNotFound $pageNotFound,
        private SecurityPolicyHeaders $securityPolicyHeaders,
    ) {
        $this->files = new Map();

        $finder = new Finder();
        $found = $finder
            ->files()
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCS(true)
            ->in(DM_PUBLIC_ROOT)
        ;

        foreach ($found as $file) {
            $this->files->put($file->getRelativePathname(), $file->getPathname());
        }
    }

    public function sendAsset(Response $response, string $asset): ?HttpResponderInterface
    {
        if (empty($asset)) {
            return $this->pageNotFound;
        }

        if (!$this->files->hasKey($asset)) {
            return $this->pageNotFound;
        }

        $this->securityPolicyHeaders->sendAssetHeaders($response);

        if ($response->sendfile($this->files->get($asset))) {
            return null;
        }

        return $this->pageNotFound;
    }
}
