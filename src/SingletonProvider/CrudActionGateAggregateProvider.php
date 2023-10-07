<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Ds\Map;
use LogicException;
use Resonance\Attribute\CrudActionSubject;
use Resonance\Attribute\DecidesCrudAction;
use Resonance\Attribute\Singleton;
use Resonance\CrudActionGate;
use Resonance\CrudActionGateAggregate;
use Resonance\CrudActionGateInterface;
use Resonance\CrudActionSubjectInterface;
use Resonance\PHPProjectFiles;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;

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
