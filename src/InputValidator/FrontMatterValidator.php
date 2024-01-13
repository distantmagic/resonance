<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidator;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\FrontMatterCollectionReference;
use Distantmagic\Resonance\InputValidatedData\FrontMatter;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\StaticPageContentType;
use Generator;

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
            next: $data->next ?? null,
            parent: $data->parent ?? null,
            registerStylesheets: $data->register_stylesheets,
            title: trim($data->title),
        );
    }

    protected function makeSchema(): JsonSchema
    {
        $contentTypes = StaticPageContentType::values();

        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'collections' => [
                    'type' => 'array',
                    'items' => [
                        'anyOf' => [
                            [
                                'type' => 'string',
                                'minLength' => 1,
                            ],
                            [
                                'type' => 'object',
                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                        'minLength' => 1,
                                    ],
                                    'next' => [
                                        'type' => 'string',
                                        'minLength' => 1,
                                    ],
                                ],
                                'required' => ['name', 'next'],
                            ],
                        ],
                    ],
                    'default' => [],
                ],
                'content_type' => [
                    'type' => 'string',
                    'enum' => $contentTypes,
                    'default' => StaticPageContentType::Markdown->value,
                ],
                'description' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'draft' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'layout' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'next' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'parent' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'register_stylesheets' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'minLength' => 1,
                    ],
                    'default' => [],
                ],
                'title' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
            ],
            'required' => ['description', 'layout', 'title'],
        ]);
    }

    /**
     * @param array<array{ name: string, next: string }|string> $collections
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
