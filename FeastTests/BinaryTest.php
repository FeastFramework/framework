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

use Feast\Binary;
use Feast\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

class BinaryTest extends TestCase
{
    protected Binary $binary;

    public function setUp(): void
    {
        $terminal = new \Feast\Terminal(false);
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $main = $this->createStub(\Feast\Interfaces\MainInterface::class);
        $container->add(\Feast\Interfaces\MainInterface::class, $main);
        $this->binary = new Binary($terminal, new \Feast\Help($terminal, ['famine']));
    }

    public function testMainHelpAll(): void
    {
        $this->binary->run(['famine', 'help'], ['famine', 'help']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Usage: php famine command options', $output);
    }

    public function testMainHelpAllAlternateUsage(): void
    {
        $terminal = new \Feast\Terminal(false);
        $this->binary = new Binary($terminal, new \Feast\Help($terminal, ['./famine']));
        $this->binary->run(['./famine', 'help'], ['./famine', 'help']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Usage: ./famine command options', $output);
    }

    public function testMainHelpFeast(): void
    {
        $this->binary->run(['famine', 'help', 'feast'], ['famine', 'help', 'feast']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith(
            'Usage: php famine command options' . "\n" . 'Available feast commands',
            trim($output)
        );
    }

    public function testMainHelpFeastCreate(): void
    {
        $this->binary->run(['famine', 'help', 'feast:create'], ['famine', 'help', 'feast:create']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith(
            'Usage: php famine command options' . "\n" . 'Available feast:create commands',
            trim($output)
        );
    }

    public function testMainHelpFeastJobs(): void
    {
        $this->binary->run(['famine', 'help', 'feast:job'], ['famine', 'help', 'feast:job']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith(
            'Usage: php famine command options' . "\n" . 'Available feast:job commands',
            trim($output)
        );
    }

    public function testMainHelpFeastMigration(): void
    {
        $this->binary->run(['famine', 'help', 'feast:migration'], ['famine', 'help', 'feast:migration']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith(
            'Usage: php famine command options' . "\n" . 'Available feast:migration commands',
            trim($output)
        );
    }

    public function testMainHelpFeastCache(): void
    {
        $this->binary->run(['famine', 'help', 'feast:cache'], ['famine', 'help', 'feast:cache']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith(
            'Usage: php famine command options' . "\n" . 'Available feast:cache commands',
            trim($output)
        );
    }

    public function testMainHelpFeastMaintenance(): void
    {
        $this->binary->run(['famine', 'help', 'feast:maintenance'], ['famine', 'help', 'feast:maintenance']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith(
            'Usage: php famine command options' . "\n" . 'Available feast:maintenance',
            trim($output)
        );
    }

    public function testMainHelpFeastCreateAction(): void
    {
        $this->binary->run(['famine', 'help', 'feast:create:action'], ['famine', 'help', 'feast:create:action']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine feast:create:action', trim($output));
    }

    public function testMainFeastHelpCreateMigration(): void
    {
        $this->binary->run(['famine', 'help', 'feast:create:migration'], ['famine', 'help', 'feast:create:migration']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine feast:migration:create', trim($output));
    }

    public function testMainFeastCreateMigration(): void
    {
        $this->binary->run(['famine', 'feast:create:migration'], ['famine', 'feast:create:migration']);
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('', trim($output));
    }

    public function testMainHelpFeastCreateModel(): void
    {
        $this->binary->run(['famine', 'help', 'feast:create:model'], ['famine', 'help', 'feast:create:model']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine feast:create:model', trim($output));
    }

    public function testMainHelpFeastMigrationCreate(): void
    {
        $this->binary->run(['famine', 'help', 'feast:migration:create'], ['famine', 'help', 'feast:migration:create']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine feast:migration:create', trim($output));
    }

    public function testMainHelpFeastMigrationUp(): void
    {
        $this->binary->run(['famine', 'help', 'feast:migration:up'], ['famine', 'help', 'feast:migration:up']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine feast:migration:up {name}', trim($output));
    }

    public function testMainHelpFeastMigrationRunAll(): void
    {
        $this->binary->run(
            ['famine', 'help', 'feast:migration:run-all'],
            ['famine', 'help', 'feast:migration:run-all']
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine feast:migration:run-all', trim($output));
    }

    public function testMainHelpHelp(): void
    {
        $this->binary->run(['famine', 'help', 'help'], ['famine', 'help', 'help']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Really? Really?!', trim($output));
    }

    public function testMainFeast(): void
    {
        $this->binary->run(['famine', 'feast'], ['famine', 'feast']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine command options', trim($output));
    }

    public function testMainCreate(): void
    {
        $this->binary->run(['famine', 'feast:create'], ['famine', 'feast:create']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine command options', trim($output));
        $this->assertStringContainsString('Available feast:create commands:', $output);
    }

    public function testMainMigration(): void
    {
        $this->binary->run(['famine', 'feast:migration'], ['famine', 'feast:migration']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine command options', trim($output));
        $this->assertStringContainsString('Available feast:migration commands:', $output);
    }

    public function testMainCache(): void
    {
        $this->binary->run(['famine', 'feast:cache'], ['famine', 'feast:cache']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine command options', trim($output));
        $this->assertStringContainsString('Available feast:cache commands:', $output);
    }

    public function testMainJob(): void
    {
        $this->binary->run(['famine', 'feast:job'], ['famine', 'feast:job']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine command options', trim($output));
        $this->assertStringContainsString('Available feast:job commands:', $output);
    }

    public function testMainMaintenance(): void
    {
        $this->binary->run(['famine', 'feast:maintenance'], ['famine', 'feast:maintenance']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine command options', trim($output));
        $this->assertStringContainsString('Available feast:maintenance commands:', $output);
    }

    public function testMainServe(): void
    {
        $this->binary->run(['famine', 'feast:serve'], ['famine', 'feast:serve']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Usage: php famine command options', trim($output));
        $this->assertStringContainsString('Available feast:serve commands:', $output);
    }

    public function testMainCreateModelRun(): void
    {
        $this->binary->run(
            ['famine', 'feast:create:model', 'TestController', 'TestAction'],
            ['famine', 'feast:create:model', 'TestController', 'TestAction']
        );
        // If we got here, our code ran through the end of main
        $this->assertTrue(true);
    }

    public function testMainMigrationUpWithArguments(): void
    {
        $this->binary->run(['famine', 'feast:migration:up', 'test'], ['famine', 'feast:migration:up', 'test']);
        // If we got here, our code ran through the end of main
        $this->assertTrue(true);
    }

    public function testMainMigrationRunAllNoArguments(): void
    {
        $this->binary->run(['famine', 'feast:migration:run-all'], ['famine', 'feast:migration:run-all']);
        // If we got here, our code ran through the end of main
        $this->assertTrue(true);
    }

    public function testMainNonFeastFunctions(): void
    {
        $this->binary->run(['famine', 'test:run'], ['famine', 'test:run']);
        // If we got here, our code ran through the end of main
        $this->assertTrue(true);
    }

    public function testMainNonFeastFunctionsException(): void
    {
        $main = di(\Feast\Interfaces\MainInterface::class);
        $main->method('main')->willThrowException(new NotFoundException('Test'));
        $this->binary->run(['famine', 'test:run'], ['famine', 'test:run']);
        // If we got here, our code ran through the end of main
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Test', trim($output));
    }

    public function testMainNonFeastCommandGroup(): void
    {
        $this->binary->run(['famine', 'small'], ['famine', 'small']);
        // If we got here, our code ran through the end of main
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals(
            'small

Usage: php famine small:create

Testing Helps

Parameters
--color=string      A random color
{name} string       Name of service to create',
            trim($output)
        );
    }
}
