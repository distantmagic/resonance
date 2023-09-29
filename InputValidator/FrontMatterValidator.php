<?php

declare(strict_types=1);

namespace Resonance\InputValidator;

use Generator;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Resonance\FrontMatterCollectionReference;
use Resonance\InputValidatedData\FrontMatter;
use Resonance\InputValidator;
use Resonance\StaticPageContentType;
use Resonance\StaticPageLayoutHandler;

/**
 * @extends InputValidator<FrontMatter, object{
 *     collections: array<string|object{ name: string, next: string }>,
 *     content_type: string,
 *     description: string,
 *     next: null|string,
 *     layout: string,
 *     parent: null|string,
 *     register_stylesheets: array<string>,
 *     title: string,
 * }>
 */
readonly class FrontMatterValidator extends InputValidator
{
    protected function castValidatedData(mixed $data): FrontMatter
    {
        $collections = iterator_to_array($this->normalizeDataCollections($data->collections));

        return new FrontMatter(
            collections: $collections,
            contentType: StaticPageContentType::from($data->content_type),
            description: trim($data->description),
            layout: StaticPageLayoutHandler::from($data->layout),
            next: $data->next,
            parent: $data->parent,
            registerStylesheets: $data->register_stylesheets,
            title: trim($data->title),
        );
    }

    protected function makeSchema(): Schema
    {
        $layouts = StaticPageLayoutHandler::values();
        $contentTypes = StaticPageContentType::values();

        return Expect::structure([
            'collections' => Expect::listOf(
                Expect::anyOf(
                    Expect::string()->min(1),
                    Expect::structure([
                        'name' => Expect::string()->min(1),
                        'next' => Expect::string()->min(1),
                    ]),
                )
            )->default([]),
            'description' => Expect::string()->min(1)->required(),
            'content_type' => Expect::anyOf(...$contentTypes)->default(StaticPageContentType::Markdown->value),
            'layout' => Expect::anyOf(...$layouts)->required(),
            'next' => Expect::string()->min(1),
            'parent' => Expect::string()->min(1),
            'register_stylesheets' => Expect::listOf(
                Expect::string()->min(1),
            )->default([]),
            'title' => Expect::string()->min(1)->required(),
        ]);
    }

    /**
     * @param array<object{ name: string, next: string }|string> $collections
     *
     * @return Generator<FrontMatterCollectionReference>
     */
    private function normalizeDataCollections(array $collections): Generator
    {
        foreach ($collections as $collection) {
            if (is_string($collection)) {
                yield new FrontMatterCollectionReference($collection, null);
            } else {
                yield new FrontMatterCollectionReference(
                    $collection->name,
                    $collection->next,
                );
            }
        }
    }
}
