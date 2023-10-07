<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use FastRoute\RouteParser\Std;
use LogicException;
use UnexpectedValueException;

readonly class TemplatedLink implements TemplatedLinkInterface
{
    use LinkTrait;

    /**
     * @var array<string, string>
     */
    private array $allowedParameters;

    private bool $expectsParameters;

    /**
     * @var array<string, string>
     */
    private array $regexps;

    private string $replaceableHref;

    public function __construct(
        private string $href,
        private array $attributes = [],
        private array $rels = [],
    ) {
        $routeParser = new Std();
        $routeChunksAggregate = $routeParser->parse($this->getHref());

        if (!isset($routeChunksAggregate[0])) {
            throw new LogicException('Unable to parse templated link.');
        }

        /**
         * @var array<array{0:string,1:string}|string>
         */
        $routeChunks = $routeChunksAggregate[0];

        $allowedParameters = [];
        $regexps = [];

        /**
         * @var array<int, string>
         */
        $replaceableHref = [];

        foreach ($routeChunks as $chunkWithRegExp) {
            if (is_array($chunkWithRegExp)) {
                list($chunk, $regexp) = $chunkWithRegExp;

                $bracketedChunk = '{'.$chunk.'}';

                $allowedParameters[$chunk] = $bracketedChunk;
                $regexps[$chunk] = $regexp;
                $replaceableHref[] = $bracketedChunk;
            } else {
                $replaceableHref[] = $chunkWithRegExp;
            }
        }

        $this->allowedParameters = $allowedParameters;
        $this->expectsParameters = !empty($this->allowedParameters);
        $this->regexps = $regexps;
        $this->replaceableHref = implode('', $replaceableHref);
    }

    public function build(?array $params = null): LinkInterface
    {
        return new Link(
            $this->buildHref($params),
            $this->getAttributes(),
            $this->getRels()
        );
    }

    public function buildHref(?array $params = null): string
    {
        if (!$this->expectsParameters && empty($params)) {
            return $this->getHref();
        }

        if (empty($params)) {
            throw new UnexpectedValueException('Link expects parameters.');
        }

        $replace = [];

        foreach ($params as $name => $value) {
            if (!isset($this->allowedParameters[$name])) {
                throw new UnexpectedValueException('No such parameter in link: '.$name);
            }
            if (!$this->isParameterValid($name, $value)) {
                throw new UnexpectedValueException('Parameter "'.$name.'" does not match route regexp.');
            }
            $replace[$this->allowedParameters[$name]] = $value;
        }

        return strtr($this->replaceableHref, $replace);
    }

    public function getAllowedParameters(): array
    {
        return array_keys($this->allowedParameters);
    }

    public function isTemplated(): true
    {
        return true;
    }

    private function isParameterValid(string $name, string $value): bool
    {
        if (!isset($this->regexps[$name])) {
            return true;
        }

        return (bool) preg_match('~^'.$this->regexps[$name].'$~', $value);
    }
}
