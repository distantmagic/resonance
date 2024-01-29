<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\TypeDeclaration\Rector\BooleanAnd\BinaryOpNullableToInstanceofRector;
use Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/src',
    ])
    ->withImportNames()
    ->withRules([
    ])
    ->withSkip([
        BinaryOpNullableToInstanceofRector::class,
        ChangeArrayPushToArrayAssignRector::class,
        ClosureToArrowFunctionRector::class,
        DisallowedEmptyRuleFixerRector::class,
        EmptyOnNullableObjectToInstanceOfRector::class,
        ExplicitBoolCompareRector::class,
        FinalizeClassesWithoutChildrenRector::class,
        InlineIfToExplicitIfRector::class,
        NewInInitializerRector::class,
        RemoveNonExistingVarAnnotationRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,
        SimplifyIfElseToTernaryRector::class,
        StringableForToStringRector::class,
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_82,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        SetList::PHP_82,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
    ])
;
