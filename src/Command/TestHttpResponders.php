<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\RespondsWith;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\HttpRecursiveResponder;
use Distantmagic\Resonance\HttpResponderAggregate;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\InspectableSwooleResponse;
use Distantmagic\Resonance\SwooleCoroutineHelper;
use Distantmagic\Resonance\TestsHttpResponseCollection;
use Ds\Map;
use RuntimeException;
use Swoole\Http\Request;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'test:http-responders',
    description: 'Test HTTP responders'
)]
final class TestHttpResponders extends Command
{
    public function __construct(
        private readonly HttpRecursiveResponder $recursiveResponder,
        private readonly HttpResponderAggregate $httpResponderAggregate,
        private readonly TestsHttpResponseCollection $testsHttpResponseCollection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var bool $isValid
         */
        $isValid = true;

        foreach ($this->testsHttpResponseCollection->httpResponder as $httpResponder => $testsHttpResponses) {
            foreach ($testsHttpResponses as $testsHttpResponse) {
                $potentialResponses = $this
                    ->testsHttpResponseCollection
                    ->testsHttpResponse
                    ->get($testsHttpResponse)
                ;

                $result = SwooleCoroutineHelper::mustRun(function () use (
                    $output,
                    $httpResponder,
                    $potentialResponses
                ): bool {
                    return $this->testResponses(
                        $output,
                        $httpResponder,
                        $potentialResponses,
                    );
                });

                if (!$result) {
                    $isValid = false;
                }
            }
        }

        if ($isValid) {
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    /**
     * @param Map<int,RespondsWith> $potentialResponses
     */
    private function testResponses(
        OutputInterface $output,
        HttpResponderInterface $httpResponder,
        Map $potentialResponses,
    ): bool {
        $output->write(sprintf('Testing <info>%s</info> ... ', $httpResponder::class));

        $request = new Request();
        $response = new InspectableSwooleResponse();

        $this->recursiveResponder->respondRecursive($request, $response, $httpResponder);

        $respondsWith = $potentialResponses->get($response->mockStatus, null);

        if (!$respondsWith) {
            throw new RuntimeException(sprintf(
                'Unhandled response status code: %d',
                $response->mockStatus,
            ));
        }

        $contentType = $response->mockGetkContentType();

        if (!str_starts_with($contentType, $respondsWith->contentType->value)) {
            throw new RuntimeException(sprintf(
                'Invalid content type: "%s", expected: "%s"',
                $contentType,
                $respondsWith->contentType->value,
            ));
        }

        $constraintResult = $respondsWith
            ->constraint
            ->validate($response->mockGetCastedContent())
        ;

        if ($constraintResult->status->isValid()) {
            $output->writeln('ok');

            return true;
        }

        $output->writeln('<error>error</error>');

        foreach ($constraintResult->getErrors() as $path => $error) {
            $output->writeln(sprintf('%s -> %s', $path, $error));
            $output->writeln(print_r($constraintResult->castedData, true));
        }

        return false;
    }
}
