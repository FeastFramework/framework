<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
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

use Feast\Attributes\Action;
use Feast\Attributes\Param;
use Feast\Enums\ParamType;

class Help
{
    private string $usage;

    /**
     * Help constructor.
     *
     * @param Terminal $terminal
     * @param array $arguments
     */
    public function __construct(private Terminal $terminal, array $arguments)
    {
        $this->setUsageName((string)$arguments[0]);
    }

    /**
     * Set usage name based on the command being executed.
     *
     * @param string $usageName
     */
    private function setUsageName(string $usageName): void
    {
        if (str_starts_with($usageName, './')) {
            $this->usage = $usageName;
        } else {
            $this->usage = 'php ' . $usageName;
        }
    }

    /**
     * Print CLI Method help.
     *
     * @param string $command
     */
    public function printCliMethodHelp(string $command): void
    {
        $command = trim($command, '\'');
        $path = '\\Modules\\CLI\\Controllers\\';
        $commandParts = explode(':', $command);
        $controller = $commandParts[0];
        $actionName = $commandParts[1] ?? '';

        if (str_starts_with($command, 'feast:')) {
            $path = '\\Feast\\Controllers\\';
            $commandParts = explode(':', substr($command, 6));
            $controller = $commandParts[0];
            $actionName = $commandParts[1] ?? '';
        }

        /** @var class-string $className */
        $className = $path . ucfirst($controller) . 'Controller';
        if ($actionName === '') {
            $this->analyzeClass($className, $command);
        } else {
            $this->analyzeMethod($className, $actionName, $command);
        }
    }

    /**
     * @param class-string $controllerClass
     * @param string $command
     */
    protected function analyzeClass(string $controllerClass, string $command): void
    {
        try {
            $class = new \ReflectionClass($controllerClass);

            $methods = $class->getMethods();
            $count = 0;
            $longestMethod = 0;
            $descriptions = [];
            $callable = '';
            foreach ($methods as $method) {
                if (str_ends_with($method->name, 'Get')) {
                    $actionAttributes = $method->getAttributes(Action::class);
                    $parameters = $method->getAttributes(Param::class);
                    $name = NameHelper::getMethodNameAsCallableAction($method->getName());
                    $callableClass = NameHelper::getControllerClassName($class);
                    $callable = $callableClass . ':' . $name;
                    foreach ($actionAttributes as $actionAttribute) {
                        /** @var Action $actionItem */
                        $actionItem = $actionAttribute->newInstance();

                        $longestMethod = $longestMethod < strlen($callable) ? strlen(
                            $callable
                        ) : $longestMethod;
                        $descriptions[$callable] = $actionItem->description;
                        $count++;
                    }
                }
            }
            if ($count === 1 && isset($actionAttributes) && isset($parameters)) {
                $this->terminal->command($command);
                /** @var array<array-key,\ReflectionAttribute<Action>> $actionAttributes */
                $this->displayActionInfo($actionAttributes, $callable);
                /** @var array<array-key,\ReflectionAttribute<Param>> $parameters */
                $this->displayParameterInfo($parameters);
            } else {
                ksort($descriptions);
                $this->terminal->message("\n" . 'Usage: ' . $this->usage . ' command options');
                $this->terminal->message('Available ' . $command . ' commands:' . "\n");
                $this->terminal->command($command);
                foreach ($descriptions as $name => $description) {
                    $this->terminal->message('  ' . str_pad($name, $longestMethod + 3) . $description);
                }
            }
        } catch (\ReflectionException) {
            $this->terminal->error('Class', false);
            $this->terminal->message(' ' . $command . ' ', false);
            $this->terminal->error('does not exist!');
        }
    }

    /**
     * @param class-string $controllerClass
     * @param string $actionName
     * @param string $command
     */
    protected function analyzeMethod(string $controllerClass, string $actionName, string $command): void
    {
        $action = lcfirst(str_replace('-', '', ucwords($actionName, '-')) . 'Get');
        try {
            $method = new \ReflectionMethod($controllerClass, $action);
            $callable = NameHelper::getControllerClassName($method->getDeclaringClass()) . ':' . $actionName;
            $actionAttributes = $method->getAttributes(Action::class);
            $parameters = $method->getAttributes(Param::class);
            $this->displayActionInfo($actionAttributes, $callable);
            $this->displayParameterInfo($parameters);
        } catch (\ReflectionException) {
            $this->terminal->error('Method', false);
            $this->terminal->message(' ' . $command . ' ', false);
            $this->terminal->error('does not exist!');
        }
    }

    /**
     * @param array<\ReflectionAttribute<Param>> $parameters
     */
    protected function displayParameterInfo(array $parameters): void
    {
        if (empty($parameters)) {
            return;
        }

        $this->terminal->command('Parameters');
        $nonFlags = [];
        foreach ($parameters as $parameter) {
            /** @var Param $parameterItem */
            $parameterItem = $parameter->newInstance();
            if ($parameterItem->paramType === ParamType::PARAM) {
                $nonFlags[] = $parameterItem->getParamText($this->terminal);
                continue;
            }
            $this->terminal->message($parameterItem->getParamText($this->terminal));
        }
        foreach ($nonFlags as $param) {
            $this->terminal->message($param);
        }
    }

    /**
     * @param array<\ReflectionAttribute<Action>> $actionAttributes
     * @param string $name
     */
    protected function displayActionInfo(array $actionAttributes, string $name): void
    {
        foreach ($actionAttributes as $actionAttribute) {
            /** @var Action $actionItem */
            $actionItem = $actionAttribute->newInstance();
            $this->terminal->message("\n" . $actionItem->getHelpText($this->usage, $name) . "\n");
            $this->terminal->message($actionItem->description . "\n");
        }
    }

}
