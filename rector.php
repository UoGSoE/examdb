<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();

    // Define what rule sets will be applied
    $parameters->set(Option::SETS, [
        SetList::DEAD_CODE,
        SetList::PHP_80,
    ]);
    $parameters->set(Option::SKIP, [
        \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector::class,
        \Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector::class,
// uncomment the next line on a first run to prevent some weirdness
//        \Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector::class,
    ]);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // register a single rule
    $services->set(\Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector::class);
    $services->set(\Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector::class);
    $services->set(\Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector::class);
    $services->set(\Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector::class);
    $services->set(\Rector\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector::class);
    $services->set(\Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector::class);
    $services->set(\Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector::class);
    $services->set(\Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector::class);
    $services->set(\Rector\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector::class);
    $services->set(\Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector::class);
    $services->set(\Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector::class);
    $services->set(\Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector::class);
    $services->set(\Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector::class);
    $services->set(\Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector::class);


};
