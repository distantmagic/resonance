<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;
use IteratorAggregate;
use RuntimeException;
use Symfony\Component\Finder\Finder;

/**
 * @template-implements IteratorAggregate<array{
 *   cookie: int,
 *   mask: int,
 *   name: string,
 *   wd: int,
 * }>
 */
readonly class InotifyIterator implements IteratorAggregate
{
    /**
     * @param array<string> $paths
     */
    public function __construct(
        private array $paths,
        /**
         * @psalm-suppress UndefinedConstant might not have inotify
         */
        private int $flags = IN_ATTRIB | IN_CREATE | IN_DELETE | IN_MODIFY | IN_MOVE,
    ) {}

    /**
     * @return Generator<array{
     *   cookie: int,
     *   mask: int,
     *   name: string,
     *   wd: int,
     * }>
     */
    public function getIterator(): Generator
    {

        $inotify = inotify_init();

        try {
            /**
             * @var array<int> $watched
             */
            $watched = [];

            foreach ($this->findPaths() as $path) {
                $watched[] = inotify_add_watch($inotify, $path, $this->flags);
            }

            $previous = null;
            $previousTimeMilis = 0;

            while (true) {
                $events = inotify_read($inotify);

                if (is_array($events)) {
                    $currentTimeMilis = $this->getCurrentMiliseconds();

                    /**
                     * @var array{
                     *   cookie: int,
                     *   mask: int,
                     *   name: string,
                     *   wd: int,
                     * } $event
                     */
                    foreach ($events as $event) {
                        $timeDiffMilis = $currentTimeMilis - $previousTimeMilis;

                        if ($previous != $event || $timeDiffMilis > 50) {
                            yield $event;
                        }

                        $previous = $event;
                        $previousTimeMilis = $currentTimeMilis;
                    }
                }
            }

            foreach ($watched as $descriptor) {
                if (!inotify_rm_watch($inotify, $descriptor)) {
                    throw new RuntimeException('Unable to remove watched file');
                }
            }
        } finally {
            fclose($inotify);
        }
    }

    /**
     * @return Generator<string>
     */
    private function findDirectories(): Generator
    {
        foreach ($this->paths as $path) {
            if (is_dir($path)) {
                yield $path;
            }
        }
    }

    /**
     * @return Generator<string>
     */
    private function findPaths(): Generator
    {
        $finder = new Finder();
        $found = $finder
            ->directories()
            ->in(iterator_to_array($this->findDirectories()))
        ;

        foreach ($found as $path) {
            yield $path->getPathname();
        }

        foreach ($this->paths as $path) {
            yield $path;
        }
    }

    private function getCurrentMiliseconds(): float
    {
        return floor(microtime(true) * 1000);
    }
}
