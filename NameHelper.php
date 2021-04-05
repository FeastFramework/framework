<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Feast;

class NameHelper
{

    /**
     * Assemble name in format for controllers/actions.
     *
     * @param string $name
     * @return string
     */
    public static function getName(string $name): string
    {
        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        return $name;
    }

    /**
     * Assemble action name with default suffix.
     *
     * @param string $name
     * @return string
     */
    public static function getDefaultAction(string $name): string
    {
        $name = self::getName($name);

        return lcfirst($name) . 'Action';
    }

    /**
     * Assemble controller name with Controller suffix.
     *
     * @param string $name
     * @return string
     */
    public static function getController(string $name): string
    {
        $name = self::getName($name);

        return $name . 'Controller';
    }

    /**
     * Get Action name with dashes.
     *
     * @param string $name
     * @return string
     */
    public static function getNameWithDashes(string $name): string
    {
        $parts = preg_split('/(?=[A-Z])/', lcfirst($name));
        return strtolower(implode('-', $parts));
    }

    /**
     * Get Action name for command line. 
     * 
     * Example: runCronItemGet returns run-cron-item.
     * 
     * @param string $name
     * @return string
     */
    public static function getMethodNameAsCallableAction(string $name): string
    {
        $parts = preg_split('/(?=[A-Z])/', lcfirst($name));
        array_pop($parts);
        return strtolower(implode('-', $parts));
    }

    /**
     * Get name of class in : syntax for controller.
     *
     * @param \ReflectionClass $class
     * @return string
     */
    public static function getControllerClassName(\ReflectionClass $class): string
    {
        $className = explode('\\', $class->name);
        $prefix = '';
        if (str_starts_with($class->name, 'Feast')) {
            $prefix = 'feast:';
        }
        $index = count($className) - 1;
        $className = lcfirst(substr($className[$index], 0, -10));

        return $prefix . $className;
    }

}
