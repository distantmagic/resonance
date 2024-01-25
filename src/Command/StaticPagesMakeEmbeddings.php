<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\LlamaCppClient;
use Distantmagic\Resonance\LlamaCppEmbeddingRequest;
use Distantmagic\Resonance\SQLiteVSSConnectionBuilder;
use Distantmagic\Resonance\StaticPageAggregate;
use Distantmagic\Resonance\StaticPageContentType;
use Distantmagic\Resonance\StaticPageMarkdownParser;
use Generator;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\StringContainerHelper;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Datasets\Labeled;
use SQLite3;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'static-pages:make-embeddings',
    description: 'Create embeddings from static pages contents (requires llama.cpp)'
)]
final class StaticPagesMakeEmbeddings extends Command
{
    private SQLite3 $embeddingsDatabase;

    public function __construct(
        private JsonSerializer $jsonSerializer,
        private LlamaCppClient $llamaCppClient,
        private StaticPageAggregate $staticPageAggregate,
        private SQLiteVSSConnectionBuilder $sqliteVSSConnectionBuilder,
        private StaticPageMarkdownParser $staticPageMarkdownParser,
    ) {
        parent::__construct();

        $this->embeddingsDatabase = $sqliteVSSConnectionBuilder->buildConnection(':memory:');
        $this->embeddingsDatabase->enableExceptions(true);
        $this->embeddingsDatabase->exec(<<<'SQL'
            CREATE VIRTUAL TABLE vss_embeddings USING vss0
            (
                embedding(4096),
            );
        SQL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $kNearestNeighbors = new KNearestNeighbors(
            k: 10,
            weighted: true,
        );
        $kNearestNeighbors->train($this->generateDataset());

        $probas = $kNearestNeighbors->probaSample($this->getEmbedding('how to add a controller'));

        foreach ($probas as $label => $proba) {
            if ($proba > 0) {
                var_dump($label);
            }
        }

        return Command::SUCCESS;
    }

    private function extractNodeTextContent(Node $node): string
    {
        $childTextContent = StringContainerHelper::getChildText($node);

        return trim(strip_tags($childTextContent));
    }

    /**
     * @return Generator<non-empty-string>
     */
    private function generateChunks(): Generator
    {
        foreach ($this->staticPageAggregate->staticPages as $staticPage) {
            if (StaticPageContentType::Html === $staticPage->frontMatter->contentType) {
                continue;
            }

            $document = $this
                ->staticPageMarkdownParser
                ->converter
                ->convert($staticPage->content)
                ->getDocument()
            ;

            yield from $this->generateChunksFromNodeChildren($document);
        }
    }

    /**
     * @return Generator<non-empty-string>
     */
    private function generateChunksFromNodeChildren(Node $node): Generator
    {
        foreach ($node->children() as $child) {
            if ($child instanceof Heading) {
                continue;
            }

            if ($child instanceof FencedCode) {
                continue;
            }

            if ($child instanceof ListBlock) {
                yield from $this->generateChunksFromNodeChildren($child);

                continue;
            }

            $textContent = $this->extractNodeTextContent($child);

            if (!empty($textContent)) {
                yield $textContent;
            }
        }
    }

    private function generateDataset(): Labeled
    {
        $samples = [];
        $labels = [];

        foreach ($this->generateEmbeddings() as $chunk => $embedding) {
            $lastRowId = $this->embeddingsDatabase->lastInsertRowID();

            $insertEmbedding = $this->embeddingsDatabase->prepare(<<<'SQL'
                INSERT INTO vss_embeddings
                (
                    rowid,
                    embedding
                )
                VALUES
                (
                    :rowid,
                    :embedding
                )
            SQL);
            $insertEmbedding->bindValue(':rowid', $lastRowId, SQLITE3_INTEGER);
            $insertEmbedding->bindValue(
                ':embedding',
                $this->jsonSerializer->serialize($embedding),
            );
            $insertEmbedding->execute();
            $insertEmbedding->close();

            $samples[] = $embedding;
            $labels[] = $chunk;
        }

        return new Labeled($samples, $labels);
    }

    /**
     * @return Generator<non-empty-string,list<float>>
     */
    private function generateEmbeddings(): Generator
    {
        foreach ($this->generateChunks() as $chunk) {
            yield $chunk => $this->getEmbedding($chunk);
        }
    }

    /**
     * @param non-empty-string $label
     *
     * @return list<float>
     */
    private function getEmbedding(string $label): array
    {
        $request = new LlamaCppEmbeddingRequest($label);

        return $this->llamaCppClient->generateEmbedding($request)->embedding;
    }
}
