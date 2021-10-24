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

namespace Feast\Controllers;

use Feast\Attributes\Action;
use Feast\CliController;
use Feast\Main;

class TemplateController extends CliController
{

    #[Action(description: 'Copy "Action" template to bin/templates folder.')]
    public function installActionGet(): void
    {
        $this->installFile('Action.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('Action.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "CliAction" template to bin/templates folder.')]
    public function installCliActionGet(): void
    {
        $this->installFile('CliAction.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('CliAction.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "Controller" template to bin/templates folder.')]
    public function installControllerGet(): void
    {
        $this->installFile('Controller.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('Controller.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "CronJob" template to bin/templates folder.')]
    public function installCronJobGet(): void
    {
        $this->installFile('CronJob.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('CronJob.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "Filter" template to bin/templates folder.')]
    public function installFilterGet(): void
    {
        $this->installFile('Filter.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('Filter.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "Form" template to bin/templates folder.')]
    public function installFormGet(): void
    {
        $this->installFile('Form.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('Form.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "Mapper" template to bin/templates folder.')]
    public function installMapperGet(): void
    {
        $this->installFile('Mapper.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('Mapper.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "Migration" template to bin/templates folder.')]
    public function installMigrationGet(): void
    {
        $this->installFile('Migration.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('Migration.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "Model" template to bin/templates folder.')]
    public function installModelGet(): void
    {
        $this->installFile('Model.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('Model.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "ModelGenerated" template to bin/templates folder.')]
    public function installModelGeneratedGet(): void
    {
        $this->installFile('ModelGenerated.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('ModelGenerated.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "Plugin" template to bin/templates folder.')]
    public function installPluginGet(): void
    {
        $this->installFile('Plugin.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('Plugin.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "QueueableJob" template to bin/templates folder.')]
    public function installQueueableJobGet(): void
    {
        $this->installFile('QueueableJob.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('QueueableJob.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "Service" template to bin/templates folder.')]
    public function installServiceGet(): void
    {
        $this->installFile('Service.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('Service.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy "Validator" template to bin/templates folder.')]
    public function installValidatorGet(): void
    {
        $this->installFile('Validator.php.txt');
        $this->terminal->message(
            'File ' . $this->terminal->commandText('Validator.php.txt') . ' has been copied to bin/templates.'
        );
    }

    #[Action(description: 'Copy all template to bin/templates folder.')]
    public function installAllGet(): void
    {
        $this->installActionGet();
        $this->installCliActionGet();
        $this->installControllerGet();
        $this->installCronJobGet();
        $this->installFilterGet();
        $this->installFormGet();
        $this->installMapperGet();
        $this->installMigrationGet();
        $this->installModelGet();
        $this->installModelGeneratedGet();
        $this->installPluginGet();
        $this->installQueueableJobGet();
        $this->installServiceGet();
        $this->installValidatorGet();
    }

    protected function installFile(string $file): void
    {
        file_put_contents(
            APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file,
            file_get_contents(
                Main::FRAMEWORK_ROOT . DIRECTORY_SEPARATOR . 'Install' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file
            )
        );
    }
}
