<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RequiresPhpExtension;
use Distantmagic\Resonance\Attribute\Singleton;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;

/**
 * Internal Doctrine class uses deprecated `VersionAwarePlatformDriver`
 * interface, so we can do nothing about that error here.
 *
 * @psalm-suppress DeprecatedInterface
 */
#[RequiresPhpExtension('pdo')]
#[Singleton]
class DoctrineMySQLDriver extends AbstractMySQLDriver
{
    use DoctrineDriverConnectTrait;
}
