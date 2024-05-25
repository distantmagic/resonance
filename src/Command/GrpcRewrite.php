<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\RequiresPhpExtension;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\CoroutineDriverInterface;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\GrpcBaseClient;
use Distantmagic\Resonance\GrpcConfiguration;
use Distantmagic\Resonance\GrpcPoolConfiguration;
use Distantmagic\Resonance\PHPFileIterator;
use Distantmagic\Resonance\PHPFileReflectionClassIterator;
use Distantmagic\Resonance\SingletonCollection;
use Grpc\BaseStub;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\Printer;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[ConsoleCommand(
    name: 'grpc:rewrite',
    description: 'Rewrite GRPC stubs'
)]
#[RequiresPhpExtension('grpc')]
#[RequiresPhpExtension('protobuf')]
final class GrpcRewrite extends CoroutineCommand
{
    public function __construct(
        CoroutineDriverInterface $coroutineDriver,
        private readonly Filesystem $filesystem,
        private readonly GrpcConfiguration $grpcConfiguration,
        private readonly Printer $printer,
    ) {
        parent::__construct($coroutineDriver);
    }

    protected function configure(): void {}

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->grpcConfiguration->poolConfiguration as $poolName => $poolConfiguration) {
            $this->rewriteGeneratedClasses($poolName, $poolConfiguration);
        }

        return Command::SUCCESS;
    }

    /**
     * Swap default GRPC stub with coroutine-safe base client
     *
     * @param non-empty-string          $poolName
     * @param ReflectioNClass<BaseStub> $reflectionClass
     */
    private function rewriteClientStub(
        string $poolName,
        GrpcPoolConfiguration $poolConfiguration,
        ReflectionClass $reflectionClass,
    ): void {
        $reflectionFilename = $reflectionClass->getFileName();

        $phpFile = PhpFile::fromCode(file_get_contents($reflectionFilename));

        foreach ($phpFile->getNamespaces() as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                if ($class instanceof ClassType) {
                    $this->rewriteClientStubClass($poolName, $class);
                }
            }
        }

        file_put_contents(
            $reflectionFilename,
            $this->printer->printFile($phpFile)
        );
    }

    /**
     * @param non-empty-string $poolName
     */
    private function rewriteClientStubClass(
        string $poolName,
        ClassType $class,
    ): void {
        $poolNameExported = var_export($poolName, true);

        $class->addComment('GENERATED FILE - DO NOT EDIT');
        $class->addComment('ORIGINALLY GENERATED BY PROTOC');
        $class->addComment('REWRITTEN BY RESONANCE GRPC:GENERATE');

        $class->addAttribute(GrantsFeature::class, [Feature::GrpcClient]);
        $class->addAttribute(Singleton::class, [
            'collection' => SingletonCollection::GrpcClient,
        ]);
        $class->addAttribute(RequiresPhpExtension::class, ['grpc']);
        $class->addAttribute(RequiresPhpExtension::class, ['protobuf']);

        $class
            ->addMethod('getGrpcPoolName')
            ->setReturnType('string')
            ->setVisibility('protected')
            ->setBody(<<<BODY
                return $poolNameExported;
            BODY)
        ;

        $class->removeMethod('__construct');
        $class->setExtends(GrpcBaseClient::class);
    }

    /**
     * Swap default GRPC stub with coroutine-safe base client
     *
     * @param non-empty-string $poolName
     */
    private function rewriteGeneratedClasses(
        string $poolName,
        GrpcPoolConfiguration $poolConfiguration,
    ): void {
        $phpFileIterator = new PHPFileIterator($poolConfiguration->outDirectory);
        $phpFileReflectionClassIterator = new PHPFileReflectionClassIterator($phpFileIterator);

        foreach ($phpFileReflectionClassIterator as $reflectionClass) {
            if ($reflectionClass->isSubclassOf(BaseStub::class)) {
                $this->rewriteClientStub(
                    $poolName,
                    $poolConfiguration,
                    $reflectionClass,
                );
            }
        }
    }
}
