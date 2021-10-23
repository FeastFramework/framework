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
use Feast\Controllers\CacheController;
use Feast\Controllers\CreateController;
use Feast\Controllers\JobController;
use Feast\Controllers\MaintenanceController;
use Feast\Controllers\MigrationController;
use Feast\Controllers\ServeController;
use Feast\Controllers\TemplateController;
use Feast\Exception\NotFoundException;
use Feast\Interfaces\MainInterface;
use ReflectionException;

class Binary
{

    private string $usage = '';

    /**
     * Binary constructor.
     *
     * @param Terminal $terminal
     * @param Help $help
     */
    public function __construct(private Terminal $terminal, private Help $help)
    {
    }

    /**
     * Main function for feast cli.
     *
     * @param array<string> $rawArguments
     * @param array<string> $arguments
     * @throws Exception\Error404Exception
     * @throws Exception\ServerFailureException
     * @throws ServiceContainer\NotFoundException|ReflectionException
     */
    public function run(array $rawArguments, array $arguments): void
    {
        $this->setUsageName($rawArguments[0]);

        if (!isset($rawArguments[1]) || $rawArguments[1] === 'help' && !isset($arguments[2])) {
            $this->terminal->message("\n" . 'Usage: ' . $this->usage . ' command options');
            $this->terminal->message('Available commands:' . "\n");
            $this->analyzeFeast();
            $this->analyzeCli();
            $this->echoHelpFunctions();

            return;
        }
        if ($arguments[1] === 'feast:create:migration') {
            // Some users might type one way or the other, this links them.
            $arguments[1] = 'feast:migration:create';
        }
        if ($rawArguments[1] === 'help' && isset($arguments[2])) {
            if ($arguments[2] === 'feast:create:migration') {
                $arguments[2] = 'feast:migration:create';
            }
            $this->help($arguments[2]);

            return;
        }

        if ($rawArguments[1] === 'feast') {
            $this->printUsage($rawArguments[1]);
            $this->analyzeFeast();
            return;
        }

        if ($rawArguments[1] === 'feast:create') {
            $this->printUsage($rawArguments[1]);
            $this->analyzeFeast([CreateController::class]);

            return;
        }
        if ($rawArguments[1] === 'feast:migration') {
            $this->printUsage($rawArguments[1]);
            $this->analyzeFeast([MigrationController::class]);
            return;
        }
        if ($rawArguments[1] === 'feast:cache') {
            $this->printUsage($rawArguments[1]);
            $this->analyzeFeast([CacheController::class]);
            return;
        }

        if ($rawArguments[1] === 'feast:job') {
            $this->printUsage($rawArguments[1]);
            $this->analyzeFeast([JobController::class]);
            return;
        }

        if ($rawArguments[1] === 'feast:maintenance') {
            $this->printUsage($rawArguments[1]);
            $this->analyzeFeast([MaintenanceController::class]);
            return;
        }

        if ($rawArguments[1] === 'feast:serve') {
            $this->printUsage($rawArguments[1]);
            $this->analyzeFeast([ServeController::class]);
            return;
        }

        if ($rawArguments[1] === 'feast:template') {
            $this->printUsage($rawArguments[1]);
            $this->analyzeFeast([TemplateController::class]);
            return;
        }

        if (str_starts_with($rawArguments[1], 'feast') === false && str_contains($rawArguments[1], ':') === false) {
            $this->help($rawArguments[1]);
            return;
        }

        array_shift($arguments);

        $this->prepareArguments($arguments);

        $_SERVER['argv'] = $arguments;
        $_SERVER['argc'] = count($arguments);
        $main = di(MainInterface::class);
        try {
            $main->main();
        } catch (NotFoundException $e) {
            $this->terminal->error($e->getMessage());
        }
    }

    protected function printUsage(string $command): void
    {
        $this->terminal->message("\n" . 'Usage: ' . $this->usage . ' command options');
        $this->terminal->message('Available ' . $command . ' commands:' . "\n");
    }

    /**
     * Print basic information about the help function.
     */
    private function echoHelpFunctions(): void
    {
        $this->terminal->message($this->terminal->commandText('help'));
        $this->terminal->message('  help [command]' . "\n");
    }

    /**
     * Prepare arguments and return a string version.
     *
     * @param array<string> $arguments
     * @return void
     */
    private function prepareArguments(array &$arguments): void
    {
        foreach ($arguments as &$argument) {
            $argument = str_replace('/', '{slash}', $argument);
        }
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
     * Display the appropriate help based on the command called.
     *
     * @param string $command
     * @throws ReflectionException
     */
    private function help(string $command): void
    {
        $commandToCheck = trim($command, '\'');

        switch ($commandToCheck) {
            case 'feast':
                $this->printUsage('feast');
                $this->analyzeFeast();
                break;
            case 'help':
                $this->terminal->message('Really? Really?!');
                break;
            default:
                $this->help->printCliMethodHelp($command);
        }
    }

    /**
     * Process description attribute and return whether any methods were found.
     *
     * @param \ReflectionMethod $method
     */
    private function processCliMethods(\ReflectionMethod $method): void
    {
        $name = NameHelper::getMethodNameAsCallableAction($method->getName());
        $class = NameHelper::getControllerClassName($method->getDeclaringClass());
        $callable = $class . ':' . $name;
        $actions = $method->getAttributes(Action::class);
        foreach ($actions as $action) {
            /** @var Action $actionItem */
            $actionItem = $action->newInstance();
            $message = str_pad('  ' . $callable, 30);
            $newLine = !str_ends_with($message, ' ');

            $this->terminal->message($message, $newLine);
            if ($newLine) {
                $this->terminal->message(str_repeat(' ', 7), false);
            }

            $this->terminal->message($actionItem->description);
        }
    }

    /**
     * Process custom actions built into Feast. Defaults to all classes.
     *
     * @param array<class-string> $classes
     * @throws ReflectionException
     */
    private function analyzeFeast(
        array $classes = [
            CreateController::class,
            MigrationController::class,
            CacheController::class,
            JobController::class,
            MaintenanceController::class,
            ServeController::class,
            TemplateController::class
        ]
    ): void {
        /** @var class-string $class */
        foreach ($classes as $class) {
            $this->processCliClass(new \ReflectionClass($class));
        }
    }

    /**
     * Process all custom actions in the CLI module.
     */
    private function analyzeCli(): void
    {
        $directory = opendir(
            APPLICATION_ROOT . 'Modules' . DIRECTORY_SEPARATOR . 'CLI' . DIRECTORY_SEPARATOR . 'Controllers'
        );

        // Loop through the directory and read all classes ending in Controller.php
        while (false !== ($classFile = readdir($directory))) {
            if (str_ends_with($classFile, 'Controller.php')) {
                // Load class info
                /** @var class-string $className */
                $className = '\\Modules\\CLI\\Controllers\\' . substr($classFile, 0, -4);
                $class = new \ReflectionClass($className);

                // Parse class and methods
                $this->processCliClass($class);
            }
        }
    }

    /**
     * Parse all descriptions for a class and return if any methods had a description.
     *
     * @param \ReflectionClass $class
     */
    private function processCliClass(\ReflectionClass $class): void
    {
        $className = NameHelper::getControllerClassName($class);
        $this->terminal->command($className);

        $hasMethods = false;
        $methods = $class->getMethods();
        // Parse methods
        foreach ($methods as $method) {
            if (str_ends_with($method->getName(), 'Get')) {
                $this->processCliMethods($method);
                $hasMethods = true;
            }
        }

        // echo blank line if methods exist.
        if ($hasMethods) {
            $this->terminal->message('');
        }
    }

}
