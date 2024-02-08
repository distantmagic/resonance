<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\TwigFunction;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigFunction as TwigFunctionAttribute;
use Distantmagic\Resonance\CrudAction;
use Distantmagic\Resonance\CrudActionSubjectInterface;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigFunction;
use Swoole\Http\Request;

#[Singleton(collection: SingletonCollection::TwigFunction)]
#[TwigFunctionAttribute]
readonly class GatekeeperCanCrud extends TwigFunction
{
    public function __construct(private Gatekeeper $gatekeeper) {}

    public function __invoke(
        Request $request,
        CrudActionSubjectInterface $subject,
        CrudAction $crudAction,
    ): bool {
        return $this->gatekeeper->withRequest($request)->canCrud($subject, $crudAction);
    }

    public function getName(): string
    {
        return 'can_crud';
    }
}
