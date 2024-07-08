---
collections: 
    - documents
layout: dm:document
parent: docs/features/ai/index
title: Machine Learning
description: >
    Incorporate Machine Learning models into your application.
---

# Machine Learning

Resonance integrates with [Rubix ML](https://rubixml.com/) to serve Machine Learning models. Rubix ML is a high-level machine learning library that allows you to train and serve machine learning models.

## Training

Put your datasets in a place from which your application can access to them (for example `datasets` directory) and prepare `models` directory for the outputs.

```php
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
```

## Serving

After the model is trained, you can serve responses from a specific responder in your application.

It must be loaded in the constructor so the model will be loaded during runtime. Resonance will keep it in the memory and serve predictions from it. You can use the same server as for the rest of your application.

```php
<?php

declare(strict_types=1);

namespace App\HttpResponder;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Estimator;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;

#[RespondsToHttp(
    method: RequestMethod::POST,
    pattern: '/predict',
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
readonly class Predict extends HttpResponder
{
    private Estimator $model;

    public function __construct()
    {
        $this->model = PersistentModel::load(new Filesystem(DM_ROOT.'/models/iris.model'));
    }

    public function respond(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): HttpInterceptableInterface {
        $dataset = new Unlabeled($request->getParsedBody());

        $predictions = $this->model->predict($dataset);

        return new JsonResponse($predictions);
    }
}
```