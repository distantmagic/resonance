<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\LlamaCppClient;
use Distantmagic\Resonance\LlamaCppEmbeddingRequest;
use Distantmagic\Resonance\SQLiteVSSConnectionBuilder;
use Distantmagic\Resonance\StaticPageChunkIterator;
use Generator;
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
        private SQLiteVSSConnectionBuilder $sqliteVSSConnectionBuilder,
        private StaticPageChunkIterator $staticPageChunkIterator,
    ) {
        parent::__construct();

        $this->embeddingsDatabase = $sqliteVSSConnectionBuilder->buildConnection(':memory:');
        $this->embeddingsDatabase->enableExceptions(true);

        $this->embeddingsDatabase->exec(<<<'SQL'
            CREATE VIRTUAL TABLE vss_embeddings USING vss0 (
                embedding(4096),
            );
        SQL);
        $this->embeddingsDatabase->exec(<<<'SQL'
            CREATE TABLE chunks (
                rowid INTEGER NOT NULL,
                chunk TEXT
            );
        SQL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->generateEmbeddings() as $chunk => $embedding) {
            $rowid = $this->embeddingsDatabase->lastInsertRowID() + 1;

            $insertEmbedding = $this->embeddingsDatabase->prepare(<<<'SQL'
                INSERT INTO vss_embeddings (
                    rowid,
                    embedding
                )
                VALUES (
                    :rowid,
                    :embedding
                )
            SQL);
            $insertEmbedding->bindValue(':rowid', $rowid, SQLITE3_INTEGER);
            $insertEmbedding->bindValue(
                ':embedding',
                $this->jsonSerializer->serialize($embedding),
            );
            $insertEmbedding->execute();
            $insertEmbedding->close();

            $insertLabel = $this->embeddingsDatabase->prepare(<<<'SQL'
                INSERT INTO chunks (
                    rowid,
                    chunk
                )
                VALUES (
                    :rowid,
                    :chunk
                )
            SQL);
            $insertLabel->bindValue(':rowid', $rowid, SQLITE3_INTEGER);
            $insertLabel->bindValue(':chunk', $chunk);
            $insertLabel->execute();
            $insertLabel->close();
        }

        $userEmbedding = $this->makeEmbedding('how to make a model in doctrine?');

        $preparedProba = $this->embeddingsDatabase->prepare(<<<'SQL'
            SELECT
                vss_embeddings.rowid,
                vss_embeddings.distance,
                chunks.chunk
            FROM vss_embeddings
            INNER JOIN chunks ON vss_embeddings.rowid = chunks.rowid
            WHERE vss_search(
                vss_embeddings.embedding,
                vss_search_params(json(:user_embedding), 20)
            )
            LIMIT 100;
        SQL);
        $preparedProba->bindValue(
            ':user_embedding',
            $this->jsonSerializer->serialize($userEmbedding)
        );

        $probaResult = $preparedProba->execute();

        while (($row = $probaResult->fetchArray(SQLITE3_ASSOC))) {
            $output->writeln(print_r($row, true));
        }

        return Command::SUCCESS;
    }

    /**
     * @return Generator<non-empty-string,list<float>>
     */
    private function generateEmbeddings(): Generator
    {
        foreach ($this->staticPageChunkIterator as $chunk) {
            yield $chunk => $this->makeEmbedding($chunk);
        }
    }

    /**
     * @param non-empty-string $label
     *
     * @return list<float>
     */
    private function makeEmbedding(string $label): array
    {
        $request = new LlamaCppEmbeddingRequest($label);

        return $this->llamaCppClient->generateEmbedding($request)->embedding;
    }
}
