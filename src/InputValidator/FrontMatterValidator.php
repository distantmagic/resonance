<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidator;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\FrontMatterCollectionReference;
use Distantmagic\Resonance\InputValidatedData\FrontMatter;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\StaticPageContentType;
use Generator;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @extends InputValidator<FrontMatter, object{
 *     collections: array<string|object{ name: string, next: string }>,
 *     content_type: string,
 *     description: string,
 *     draft: bool,
 *     next: null|string,
 *     layout: string,
 *     parent: null|string,
 *     register_stylesheets: array<string>,
 *     title: string,
 * }>
 */
#[Singleton]
readonly class FrontMatterValidator extends InputValidator
{
    protected function castValidatedData(mixed $data): FrontMatter
    {
        $collections = iterator_to_array($this->normalizeDataCollections($data->collections));

        return new FrontMatter(
            collections: $collections,
            contentType: StaticPageContentType::from($data->content_type),
            description: trim($data->description),
            isDraft: $data->draft,
            layout: $data->layout,
            next: $data->next,
            parent: $data->parent,
            registerStylesheets: $data->register_stylesheets,
            title: trim($data->title),
        );
    }

    protected function makeSchema(): Schema
    {
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
            'content_type' => Expect::anyOf(...$contentTypes)->default(StaticPageContentType::Markdown->value),
            'description' => Expect::string()->min(1)->required(),
            'draft' => Expect::bool()->default(false),
            'layout' => Expect::string()->min(1)->required(),
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
