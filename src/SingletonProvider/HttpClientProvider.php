<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Swoole has preconfigured hooks that make all curl operations  asynchronous.
 *
 * @template-extends SingletonProvider<HttpClientInterface>
 */
#[Singleton(provides: HttpClientInterface::class)]
final readonly class HttpClientProvider extends SingletonProvider
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpClientInterface
    {
        $curlHttpClient = new CurlHttpClient();
        $curlHttpClient->setLogger($this->logger);

        return $curlHttpClient;
    }
}
