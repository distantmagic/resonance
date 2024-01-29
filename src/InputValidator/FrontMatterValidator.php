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
use RuntimeException;

/**
 * @extends InputValidator<FrontMatter, object{
 *     collections: array<non-empty-string|object{ name: non-empty-string, next: non-empty-string }>,
 *     content_type: non-empty-string,
 *     description: non-empty-string,
 *     draft: bool,
 *     next: null|non-empty-string,
 *     layout: non-empty-string,
 *     parent: null|non-empty-string,
 *     register_stylesheets: array<non-empty-string>,
 *     title: non-empty-string,
 * }>
 */
#[Singleton]
readonly class FrontMatterValidator extends InputValidator
{
    public function castValidatedData(mixed $data): FrontMatter
    {
        $collections = iterator_to_array($this->normalizeDataCollections($data->collections));

        $description = trim($data->description);

        if (empty($description)) {
            throw new RuntimeException('Description cannot be empty');
        }

        $title = trim($data->title);

        if (empty($title)) {
            throw new RuntimeException('Title cannot be empty');
        }

        return new FrontMatter(
            collections: $collections,
            contentType: StaticPageContentType::from($data->content_type),
            description: $description,
            isDraft: $data->draft,
            layout: $data->layout,
            next: $data->next ?? null,
            parent: $data->parent ?? null,
            registerStylesheets: $data->register_stylesheets,
            title: $title,
        );
    }

    public function getSchema(): JsonSchema
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
     * @param array<non-empty-string|object{ name: non-empty-string, next: non-empty-string }> $collections
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
