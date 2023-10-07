<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Ds\Map;
use LogicException;
use Distantmagic\Resonance\Attribute\CrudActionSubject;
use Distantmagic\Resonance\Attribute\DecidesCrudAction;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\CrudActionGate;
use Distantmagic\Resonance\CrudActionGateAggregate;
use Distantmagic\Resonance\CrudActionGateInterface;
use Distantmagic\Resonance\CrudActionSubjectInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<CrudActionGateAggregate>
 */
#[Singleton(
    provides: CrudActionGateAggregate::class,
    requiresCollection: SingletonCollection::CrudActionGate,
)]
final readonly class CrudActionGateAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): CrudActionGateAggregate
    {
        /**
         * @var Map<class-string<CrudActionGateInterface>,CrudActionGateInterface>
         */
        $crudActionGates = new Map();

        $crudActionGateAggregate = new CrudActionGateAggregate();

        foreach ($this->collectCrudActionGates($singletons) as $deciderAttribute) {
            $crudActionGates->put(
                $deciderAttribute->singleton::class,
                $deciderAttribute->singleton,
            );
        }

        foreach ($phpProjectFiles->findByAttribute(CrudActionSubject::class) as $subjectAttribute) {
            $crudSubjectClass = $subjectAttribute->reflectionClass->getName();

            if (!is_a($crudSubjectClass, CrudActionSubjectInterface::class, true)) {
                throw new LogicException('Provided class-string is not a CRUD subject interface: '.$crudSubjectClass);
            }

            if (!is_a($subjectAttribute->attribute->gate, CrudActionGateInterface::class, true)) {
                throw new LogicException('Provided class-string is not a CRUD gate interface: '.$subjectAttribute->attribute->gate);
            }

            $crudActionGateAggregate->crudActionGates->put(
                $crudSubjectClass,
                $crudActionGates->get($subjectAttribute->attribute->gate),
            );
        }

        return $crudActionGateAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<CrudActionGate,DecidesCrudAction>>
     */
    private function collectCrudActionGates(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            CrudActionGate::class,
            DecidesCrudAction::class,
        );
    }
}
