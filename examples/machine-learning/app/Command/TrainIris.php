<?php

declare(strict_types=1);

namespace App\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Psr\Log\LoggerInterface;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\NDJSON;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Serializers\RBX;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see https://github.com/RubixML/Iris
 */
#[ConsoleCommand(
    name: 'train:iris',
    description: 'Train Iris Flower Classifier'
)]
final class TrainIris extends Command
{
    public function __construct(private LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('Loading data into memory');

        $training = Labeled::fromIterator(new NDJSON(DM_ROOT.'/datasets/iris.ndjson'));

        $testing = $training->randomize()->take(10);

        $estimator = new KNearestNeighbors(5);

        $this->logger->info('Training');

        $estimator->train($training);

        $this->logger->info('Making predictions');

        $predictions = $estimator->predict($testing);

        $metric = new Accuracy();

        $score = $metric->score($predictions, $testing->labels());

        $this->logger->info("Accuracy is $score");
        $this->logger->info('Serializing');

        $serializer = new RBX();
        $encoding = $serializer->serialize($estimator);

        $filesystemPersister = new Filesystem(DM_ROOT.'/models/iris.model');
        $filesystemPersister->save($encoding);

        return Command::SUCCESS;
    }
}
