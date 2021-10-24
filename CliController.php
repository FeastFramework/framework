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

use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\ControllerInterface;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainer;

abstract class CliController implements ControllerInterface
{
    protected Terminal $terminal;
    protected CliArguments $cliArguments;

    /**
     * @throws NotFoundException
     */
    public function __construct(
        ServiceContainer $di,
        ?ConfigInterface $config = null,
        ?CliArguments $cliArguments = null
    ) {
        $config ??= $di->get(ConfigInterface::INTERFACE_NAME);
        $this->cliArguments = $cliArguments ?? new CliArguments([]);
        /** @var bool|null $setting */
        $setting = $config->getSetting('ttycolor', null);
        $this->terminal = new Terminal($setting);
    }

    /**
     * Initialize Controller - return false if not runnable for any reason.
     *
     * @return bool
     */
    public function init(): bool
    {
        return true;
    }

    protected function help(string $command): void
    {
        $help = new Help($this->terminal, $this->cliArguments->getArguments());
        $help->printCliMethodHelp($command);
    }

    /**
     * Check for JSON allowed. Always false for CLI.
     *
     * @param string $actionName
     * @return bool
     */
    final public function alwaysJson(string $actionName): bool
    {
        return false;
    }

    protected function isValidName(string $name): bool
    {
        return preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $name) === 1;
    }

    protected function validateRulesOrPrintError(string $name, string $type = 'class'): bool
    {
        if ($this->isValidName($name) === false) {
            $this->terminal->error($name . ' is not a valid ' . $type . ' name');
            $this->terminal->error(
                'Must start with a letter or underscore and consist of letters, numbers, and underscores'
            );
            return false;
        }
        return true;
    }
}
