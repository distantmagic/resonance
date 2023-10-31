<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;

class DoctrineAttributeDriver extends AttributeDriver
{
    /**
     * We are not goint to use search paths. Instead we will just precache all
     * the classnames. Resonance is doing this anyway.
     *
     * The most important thing is: we avoid Doctrine's internal `require_once`
     * call.
     */
    public function __construct()
    {
        parent::__construct([]);

        $this->classNames = [];
    }

    /**
     * @param class-string $className
     */
    public function addClassName(string $className): void
    {
        array_push($this->classNames, $className);
    }
}
