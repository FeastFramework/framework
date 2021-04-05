<?php

declare(strict_types=1);

use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainer;

/**
 * Returns either the ServiceContainer (if no arguments) or the requested class' object
 * from the ServiceContainer.
 * 
 * @template returned
 * @param returned $className
 * @psalm-param returned::class $className
 * @param mixed ...$arguments
 * @return returned
 * @throws NotFoundException
 */
function di(?string $className = null, mixed ...$arguments): object
{
    static $serviceContainer = null;
    $serviceContainer ??= new ServiceContainer();
    if ($className === null) {
        if (isset($arguments[0]) && $arguments[0] === \Feast\Enums\ServiceContainer::CLEAR_CONTAINER) {
            $serviceContainer = new ServiceContainer();
        }
        return $serviceContainer;
    }
    return $serviceContainer->get($className, ...$arguments);
}
