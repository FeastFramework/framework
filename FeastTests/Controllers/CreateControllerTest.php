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
use Feast\Controllers\CreateController;
use Feast\Controllers\FileData;
use Feast\Database\DatabaseFactory;
use Feast\Database\FieldDetails;
use Feast\Database\TableDetails;
use Feast\Date;
use Feast\Enums\ServiceContainer;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\DatabaseInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

class CreateControllerTest extends TestCase
{

    public function setUp(): void
    {
        FileData::reset();
    }

    public function testServiceGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:service'])
        );
        $controller->serviceGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringEndsWith('Name of service to create', trim($output));
    }

    public function testServiceGetBadName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:service'])
        );
        $controller->serviceGet('1Test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('1Test is not a valid class name', trim($output));
    }

    public function testServiceGetAlreadyExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:service'])
        );
        $controller->serviceGet('successService');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('SuccessService.php already exists.', trim($output));
    }

    public function testServiceGetCreated(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:service'])
        );
        $controller->serviceGet('NewService');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Service file NewService.php created.', trim($output));
    }

    public function testFormGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form'])
        );
        $controller->formGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringEndsWith('Name of form to create', trim($output));
    }

    public function testFormGetBadName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form'])
        );
        $controller->formGet('1Test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('1Test is not a valid class name', trim($output));
    }

    public function testFormGetAlreadyExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form'])
        );
        $controller->formGet('successForm');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('SuccessForm.php already exists.', trim($output));
    }

    public function testFormGetCreated(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form'])
        );
        $controller->formGet('NewForm');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Form file NewForm.php created.', trim($output));
    }

    public function testFormFilterGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form-filter'])
        );
        $controller->formFilterGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringEndsWith('Name of filter to create', trim($output));
    }

    public function testFormFilterGetBadName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form-filter'])
        );
        $controller->formFilterGet('1Test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('1Test is not a valid class name', trim($output));
    }

    public function testFormFilterGetAlreadyExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form-filter'])
        );
        $controller->formFilterGet('successFormFilter');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('SuccessFormFilter.php already exists.', trim($output));
    }

    public function testFormFilterGetCreated(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form-filter'])
        );
        $controller->formFilterGet('NewFormFilter');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Filter file NewFormFilter.php created.', trim($output));
    }

    public function testFormValidatorGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form-validator'])
        );
        $controller->formValidatorGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringEndsWith('Name of validator to create', trim($output));
    }

    public function testFormValidatorBadName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form-validator'])
        );
        $controller->formValidatorGet('1Test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('1Test is not a valid class name', trim($output));
    }

    public function testFormValidatorGetAlreadyExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form-validator'])
        );
        $controller->formValidatorGet('successFormValidator');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('SuccessFormValidator.php already exists.', trim($output));
    }

    public function testFormValidatorGetCreated(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:form-validator'])
        );
        $controller->formValidatorGet('NewFormValidator');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Validator file NewFormValidator.php created.', trim($output));
    }

    public function testCronJobGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:cron-job'])
        );
        $controller->cronJobGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringEndsWith('Name of job to create', trim($output));
    }

    public function testCronJobGetBadName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:cron-job'])
        );
        $controller->cronJobGet('1Test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('1Test is not a valid class name', trim($output));
    }

    public function testCronJobGetAlreadyExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:cron-job'])
        );
        $controller->cronJobGet('successJob');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('SuccessJob.php already exists.', trim($output));
    }

    public function testCronJobGetCreated(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:cron-job'])
        );
        $controller->cronJobGet('NewJob');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('CronJob file NewJob.php created.', trim($output));
    }

    public function testQueueableJobGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:queueable-job'])
        );
        $controller->queueableJobGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringEndsWith('Name of job to create', trim($output));
    }

    public function testQueueableJobGetBadName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:queueable-job'])
        );
        $controller->queueableJobGet('1Test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('1Test is not a valid class name', trim($output));
    }

    public function testQueueableJobGetAlreadyExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:queueable-job'])
        );
        $controller->queueableJobGet('successJob');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('SuccessJob.php already exists.', trim($output));
    }

    public function testQueueableJobGetCreated(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:queueable-job'])
        );
        $controller->queueableJobGet('NewJob');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('QueueableJob file NewJob.php created.', trim($output));
    }

    public function testPluginGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:plugin'])
        );
        $controller->pluginGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringEndsWith('Name of plugin to create', trim($output));
    }

    public function testPluginGetBadName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:plugin'])
        );
        $controller->pluginGet('1Test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('1Test is not a valid class name', trim($output));
    }

    public function testPluginGetAlreadyExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:plugin'])
        );
        $controller->pluginGet('successService');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('SuccessService.php already exists.', trim($output));
    }

    public function testPluginGetCreated(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:plugin'])
        );
        $controller->pluginGet('NewService');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Plugin file NewService.php created.', trim($output));
    }

    public function testActionGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $controller->actionGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Create a new controller action from the template file.', trim($output));
    }

    public function testActionGetBadControllerName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $controller->actionGet('1Test', 'test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('1Test is not a valid controller name', trim($output));
    }

    public function testActionGetBadActionName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $controller->actionGet('test', '1Test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('1Test is not a valid action name', trim($output));
    }

    public function testActionGetBadModuleName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $controller->actionGet('test', 'test', module: '1Test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('1Test is not a valid module name', trim($output));
    }

    public function testActionGetActionEmpty(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $controller->actionGet('Success');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Create a new controller action from the template file.', trim($output));
    }

    public function testActionGetControllerEmpty(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $controller->actionGet(null, 'index');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Create a new controller action from the template file.', trim($output));
    }

    public function testActionGetControllerPathExistsAndControllerExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $this->writeTempController(
            APPLICATION_ROOT . 'Modules/success/Controllers/SuccessController.php',
            '\\Modules\\success',
            'Success'
        );
        $controller->actionGet('Success', 'index', module: 'success');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Action indexGet created', trim($output));
        $this->assertStringContainsString('use Feast\\Controller', trim($output));
        $this->assertStringNotContainsString('use Feast\Attributes\Action;', trim($output));
    }

    public function testActionGetControllerPathExistsAndControllerCliExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $this->writeTempController(
            APPLICATION_ROOT . 'Modules/CLI/Controllers/SuccessController.php',
            '\\Modules\\CLI',
            'Success'
        );
        $controller->actionGet('Success', 'index', module: 'CLI');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Action indexGet created', trim($output));
        $this->assertStringContainsString('use Feast\\CliController', trim($output));
        $this->assertStringContainsString('use Feast\Attributes\Action;', trim($output));
    }

    public function testCliActionGetControllerPathExistsAndControllerCliExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $this->writeTempController(
            APPLICATION_ROOT . 'Modules/CLI/Controllers/SuccessController.php',
            '\\Modules\\CLI',
            'Success'
        );
        $controller->cliActionGet('Success', 'index');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Action indexGet created', trim($output));
        $this->assertStringContainsString('use Feast\\CliController', trim($output));
        $this->assertStringContainsString('use Feast\Attributes\Action;', trim($output));
    }

    public function testActionGetControllerPathExistsControllerExistsActionExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $this->writeTempController(
            APPLICATION_ROOT . 'Modules/success/Controllers/SuccessController.php',
            '\\Modules\\success',
            'Success',
            'indexGet'
        );
        $controller->actionGet('Success', 'index', module: 'success');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Action indexGet already exists', trim($output));
    }

    public function testActionGetMangledController(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $this->writeTempController(
            APPLICATION_ROOT . 'Modules/success/Controllers/SuccessController.php',
            '\\Modules\\success',
            'Success',
            null,
            true
        );
        $controller->actionGet('Success', 'index', module: 'success');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Controller file appears corrupted', trim($output));
    }

    public function testActionGetControllerPathNotExistsControllerFileExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );
        $this->writeTempController(
            APPLICATION_ROOT . 'Modules/Failure/Controllers/SuccessController.php',
            '\\Modules\\Failure',
            'Success'
        );
        $controller->actionGet('Success', 'index', module: 'Failure');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Modules/Failure/Controllers/ created', trim($output));
        $this->assertStringNotContainsString('Controller.php created', trim($output));
    }

    public function testActionGetControllerPathNotExistsControllerFileNotExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:action'])
        );

        $controller->actionGet('Failure', 'index', module: 'Failure');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Modules/Failure/Controllers/ created', trim($output));
        $this->assertStringContainsString('Controller.php created', trim($output));
        $this->assertStringContainsString('Action indexGet created', trim($output));
        $this->assertStringContainsString('index.phtml created', trim($output));
    }

    protected function writeTempController(
        string $fileName,
        string $namespace,
        string $controllerName,
        ?string $actionName = null,
        bool $mangled = false
    ): void {
        $contents = file_get_contents(
            APPLICATION_ROOT . 'bin' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'Controller.php.txt'
        );
        $actionUse = $namespace === '\\Modules\\CLI' ? 'use Feast\Attributes\Action;' . "\n" : '';
        $cliUse = $namespace === '\\Modules\\CLI' ? 'Cli' : '';
        $contents = str_replace(
            [
                '{namespace}',
                '{name}',
                '{action-use}',
                '{cli}'
            ],
            [$namespace, ucfirst($controllerName), $actionUse, $cliUse],
            $contents
        );
        if ($actionName !== null) {
            $lastPos = strrpos($contents, '}');
            $contents = substr($contents, 0 - $lastPos) . 'public function ' . $actionName . '(): void {}' . "\n" . '}';
        }
        if ($mangled) {
            $contents = 'BrokenFile';
        }

        $this->writeTempFile($fileName, $contents);
    }

    public function testCreateModel(): void
    {
        $config = $this->createStub(ConfigInterface::class);

        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:model'])
        );
        $dbFactory = $this->createStub(DatabaseFactory::class);
        $connection = $this->createStub(DatabaseInterface::class);

        $tableInfo = new TableDetails(
            false, 'int', 'user_id', [
                     new FieldDetails('user_id', 'tinyint', 'int', 'int'),
                     new FieldDetails('created_at', 'datetime', '?\\' . Date::class, Date::class),
                     new FieldDetails('username', 'varchar(255)', 'string', 'string')
                 ]
        );
        $connection->method('getDescribedTable')->willReturn($tableInfo);

        $dbFactory->method('getConnection')->willReturn($connection);

        $controller->modelGet($dbFactory, 'users2', model: 'user');
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals(
            file_get_contents(APPLICATION_ROOT . 'ExpectedOutputs' . DIRECTORY_SEPARATOR . 'testCreateModel.txt'),
            $output
        );
    }

    public function testCreateModelBadName(): void
    {
        $config = $this->createStub(ConfigInterface::class);

        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:model'])
        );
        $dbFactory = $this->createStub(DatabaseFactory::class);

        $controller->modelGet($dbFactory, 'users2', model: '1user');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith(
            '1user is not a valid class name',
            $output
        );
    }

    public function testCreateModelCompoundPrimary(): void
    {
        $config = $this->createStub(ConfigInterface::class);

        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:model'])
        );
        $dbFactory = $this->createStub(DatabaseFactory::class);
        $connection = $this->createStub(DatabaseInterface::class);
        $tableInfo = new TableDetails(
            true, 'int', 'user_id', [
                    new FieldDetails('user_id', 'tinyint', 'int', 'int'),
                    new FieldDetails('created_at', 'datetime', '?\\' . Date::class, Date::class),
                    new FieldDetails('username', 'varchar(255)', 'string', 'string')
                ]
        );
        $connection->method('getDescribedTable')->willReturn($tableInfo);
        $dbFactory->method('getConnection')->willReturn($connection);
        $controller->modelGet($dbFactory, 'users2', model: 'user');
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals(
            file_get_contents(
                APPLICATION_ROOT . 'ExpectedOutputs' . DIRECTORY_SEPARATOR . 'testCreateModelCompoundPrimary.txt'
            ),
            $output
        );
    }

    public function testCreateModelNoPrimary(): void
    {
        $config = $this->createStub(ConfigInterface::class);

        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:model'])
        );
        $dbFactory = $this->createStub(DatabaseFactory::class);
        $connection = $this->createStub(DatabaseInterface::class);
        $tableInfo = new TableDetails(
            false, null, null, [
                     new FieldDetails('user_id', 'tinyint', 'int', 'int'),
                     new FieldDetails('created_at', 'datetime', '?\\' . Date::class, Date::class),
                     new FieldDetails('username', 'varchar(255)', 'string', 'string')
                 ]
        );
        $connection->method('getDescribedTable')->willReturn($tableInfo);
        $dbFactory->method('getConnection')->willReturn($connection);

        $controller->modelGet($dbFactory, 'users2', model: 'user');
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals(
            file_get_contents(
                APPLICATION_ROOT . 'ExpectedOutputs' . DIRECTORY_SEPARATOR . 'testCreateModelNoPrimary.txt'
            ),
            $output
        );
    }

    protected function buildTestField(string $field, string $type, string $phpType): stdClass
    {
        $return = new stdClass();
        $return->name = $field;
        $return->type = $type;
        $return->phpType = $phpType;
        return $return;
    }

    public function testCreateModelNoName(): void
    {
        $config = $this->createStub(ConfigInterface::class);

        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new CreateController(
            di(null, ServiceContainer::CLEAR_CONTAINER),
            $config,
            new CliArguments(['famine', 'feast:create:model'])
        );
        $dbFactory = $this->createStub(DatabaseFactory::class);
        $controller->modelGet($dbFactory);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Create a model and mapper for a database table.', $output);
    }

    protected function writeTempFile(string $name, string $contents): void
    {
        FileData::$files[$name] = $contents;
    }

}
