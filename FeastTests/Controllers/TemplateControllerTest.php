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

namespace Controllers;

use Feast\CliArguments;
use Feast\Config\Config;
use Feast\Controllers\TemplateController;
use PHPUnit\Framework\TestCase;

class TemplateControllerTest extends TestCase
{
    public function testInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-action'])
        );
        $controller->installActionGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Action.php.txt', $output);
        $this->assertStringContainsString('{action}{type}', $output);
    }

    public function testCliActionInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-cli-action'])
        );
        $controller->installCliActionGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('CliAction.php.txt', $output);
        $this->assertStringContainsString('{action}{type}', $output);
    }

    public function testControllerInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-controller'])
        );
        $controller->installControllerGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Controller.php.txt', $output);
        $this->assertStringContainsString('class {name}Controller extends {cli}Controller', $output);
    }

    public function testCronJobAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-cron-job'])
        );
        $controller->installCronJobGet();;
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('CronJob.php.txt', $output);
        $this->assertStringContainsString('public function run(): bool', $output);
    }

    public function testFilterInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-filter'])
        );
        $controller->installFilterGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Filter.php.txt', $output);
        $this->assertStringContainsString('public static function filter', $output);
    }

    public function testFormInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-form'])
        );
        $controller->installFormGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Form.php.txt', $output);
        $this->assertStringContainsString('parent::__construct(\'name\', \'url\', \'post\');', $output);
    }

    public function testMapperInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-mapper'])
        );
        $controller->installMapperGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Mapper.php.txt', $output);
        $this->assertStringContainsString('protected const OBJECT_NAME', $output);
    }

    public function testMigrationInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-migration'])
        );
        $controller->installMigrationGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Migration.php.txt', $output);
        $this->assertStringContainsString('protected const NAME', $output);
    }

    public function testModelInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-model'])
        );
        $controller->installModelGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Model.php.txt', $output);
        $this->assertStringContainsString('// PLACE CUSTOM MODEL CODE HERE', $output);
    }

    public function testModelGeneratedInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-model-generated'])
        );
        $controller->installModelGeneratedGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('ModelGenerated.php.txt', $output);
        $this->assertStringContainsString('extends BaseModel', $output);
    }

    public function testPluginInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-plugin'])
        );
        $controller->installPluginGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Plugin.php.txt', $output);
        $this->assertStringContainsString('public function preDispatch()', $output);
    }

    public function testQueueableJobInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-queueable-job'])
        );
        $controller->installQueueableJobGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('QueueableJob.php.txt', $output);
        $this->assertStringContainsString('public function run(): bool', $output);
    }

    public function testServiceInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-service'])
        );
        $controller->installServiceGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Service.php.txt', $output);
        $this->assertStringContainsString('extends Service', $output);
    }

    public function testValidatorInstallAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-validator'])
        );
        $controller->installValidatorGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Validator.php.txt', $output);
        $this->assertStringContainsString('public static function validate', $output);
    }

    public function testInstallAllAction(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);

        $controller = new TemplateController(
            di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:template:install-validator'])
        );
        $controller->installAllGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Action.php.txt', $output);
        $this->assertStringContainsString('CliAction.php.txt', $output);
        $this->assertStringContainsString('Controller.php.txt', $output);
        $this->assertStringContainsString('CronJob.php.txt', $output);
        $this->assertStringContainsString('Filter.php.txt', $output);
        $this->assertStringContainsString('Form.php.txt', $output);
        $this->assertStringContainsString('Mapper.php.txt', $output);
        $this->assertStringContainsString('Migration.php.txt', $output);
        $this->assertStringContainsString('Model.php.txt', $output);
        $this->assertStringContainsString('ModelGenerated.php.txt', $output);
        $this->assertStringContainsString('Plugin.php.txt', $output);
        $this->assertStringContainsString('QueueableJob.php.txt', $output);
        $this->assertStringContainsString('Service.php.txt', $output);
        $this->assertStringContainsString('Validator.php.txt', $output);
    }
}
