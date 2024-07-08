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
