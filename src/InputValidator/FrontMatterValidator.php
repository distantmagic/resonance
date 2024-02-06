<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidator;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\AnyOfConstraint;
use Distantmagic\Resonance\Constraint\BooleanConstraint;
use Distantmagic\Resonance\Constraint\EnumConstraint;
use Distantmagic\Resonance\Constraint\ListConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\FrontMatterCollectionReference;
use Distantmagic\Resonance\InputValidatedData\FrontMatter;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\StaticPageContentType;
use Generator;
use RuntimeException;

/**
 * @extends InputValidator<FrontMatter, array{
 *     collections: array<non-empty-string|array{ name: non-empty-string, next: non-empty-string }>,
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
#[Singleton(collection: SingletonCollection::InputValidator)]
readonly class FrontMatterValidator extends InputValidator
{
    public function castValidatedData(mixed $data): FrontMatter
    {
        $collections = iterator_to_array($this->normalizeDataCollections($data['collections']));

        $description = trim($data['description']);

        if (empty($description)) {
            throw new RuntimeException('Description cannot be empty');
        }

        $title = trim($data['title']);

        if (empty($title)) {
            throw new RuntimeException('Title cannot be empty');
        }

        return new FrontMatter(
            collections: $collections,
            contentType: StaticPageContentType::from($data['content_type']),
            description: $description,
            isDraft: $data['draft'],
            layout: $data['layout'],
            next: $data['next'] ?? null,
            parent: $data['parent'] ?? null,
            registerStylesheets: $data['register_stylesheets'],
            title: $title,
        );
    }

    public function getConstraint(): Constraint
    {
        $contentTypes = StaticPageContentType::values();

        return new ObjectConstraint([
            'collections' => new ListConstraint(
                valueConstraint: new AnyOfConstraint([
                    new StringConstraint(),
                    new ObjectConstraint(
                        properties: [
                            'name' => new StringConstraint(),
                            'next' => new StringConstraint(),
                        ],
                    ),
                ]),
            ),
            'content_type' => (new EnumConstraint($contentTypes))->default(StaticPageContentType::Markdown->value),
            'description' => new StringConstraint(),
            'draft' => (new BooleanConstraint())->default(false),
            'layout' => new StringConstraint(),
            'next' => (new StringConstraint())->nullable(),
            'parent' => (new StringConstraint())->nullable(),
            'register_stylesheets' => new ListConstraint(valueConstraint: new StringConstraint()),
            'title' => new StringConstraint(),
        ]);
    }

    /**
     * @param array<array{ name: non-empty-string, next: non-empty-string }|non-empty-string> $collections
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
                    $collection['name'],
                    $collection['next'],
                );
            }
        }
    }
}
