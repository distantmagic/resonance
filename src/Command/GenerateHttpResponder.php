<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SingletonCollection;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\Printer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'generate:http-responder',
    description: 'Generate http-responder'
)]
final class GenerateHttpResponder extends Command
{
    public function __construct(private readonly Printer $printer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('class_name', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $className = $input->getArgument('class_name');

        if (!is_string($className)) {
            $output->writeln('<error>class_name is not a string</error>');

            return Command::FAILURE;
        }

        $outputFilename = DM_APP_ROOT.'/HttpResponder/'.$className.'.php';

        if (file_exists($outputFilename)) {

            return Command::FAILURE;
        }

        $classCode = $this->generateHttpResponderCode($className);

        file_put_contents($outputFilename, $classCode);

        $output->writeln(sprintf('<info>Responder successfully created: %s</info>', $outputFilename));

        return Command::SUCCESS;
    }

    private function generateHttpResponderCode(string $className): string
    {
        $phpFile = new PhpFile();
        $phpFile->setStrictTypes();

        $namespace = $phpFile->addNamespace('App\\HttpResponder');
        $namespace->addUse(HttpInterceptableInterface::class);
        $namespace->addUse(HttpResponder::class);
        $namespace->addUse(HttpResponderInterface::class);
        $namespace->addUse(ServerRequestInterface::class);
        $namespace->addUse(ResponseInterface::class);
        $namespace->addUse(Singleton::class);
        $namespace->addUse(SingletonCollection::class);

        $class = $namespace->addClass($className);
        $class->addAttribute(Singleton::class, [
            'collection' => new Literal('SingletonCollection::HttpResponder'),
        ]);
        $class->setExtends(HttpResponder::class);
        $class->setFinal(true);
        $class->setReadOnly(true);

        $method = $class->addMethod('respond');
        $method->setReturnType(implode('|', [
            'null',
            HttpInterceptableInterface::class,
            HttpResponderInterface::class,
        ]));
        $method->addParameter('request')->setType(ServerRequestInterface::class);
        $method->addParameter('response')->setType(ResponseInterface::class);
        $method->setBody(<<<'CODE'
        $response->end('Hello, Resonance!');

        return null;
        CODE);

        return $this->printer->printFile($phpFile);
    }
}
