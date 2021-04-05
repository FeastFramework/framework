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

namespace Logger;

use Feast\Config\Config;
use Feast\Enums\LogLevelCode;
use Feast\Logger\Logger;
use Feast\Main;
use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

class LoggerTest extends TestCase
{
    public function testConstruct(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnMap(
            [
                [
                    'log.level',
                    LogLevel::ERROR,
                    LogLevel::DEBUG
                ],
                [
                    'log.permissions.path',
                    '0755',
                    '0755'
                ],
                [
                    'log.permissions.file',
                    '0666',
                    '0666'
                ]
            ]
        );
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $this->assertTrue($logger instanceof Logger);
    }

    public function testConstructInvalidLevel(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn('FEASTYBOYS');
        $this->expectException(InvalidArgumentException::class);
        new Logger($config, Main::RUN_AS_CLI);
    }

    public function testEmergency(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->emergency('This is an emergency');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('EMERGENCY: This is an emergency', $output);
    }

    public function testAlert(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->alert('This is an alert');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('ALERT: This is an alert', $output);
    }

    public function testWarning(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->warning('This is a warning');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('WARNING: This is a warning', $output);
    }

    public function testError(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->error('This is an error');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('ERROR: This is an error', $output);
    }

    public function testInfo(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->info('This is info');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('INFO: This is info', $output);
    }

    public function testDebug(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->debug('This is unimportant');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('DEBUG: This is unimportant', $output);
    }

    public function testCritical(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->critical('This is critical');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('CRITICAL: This is critical', $output);
    }

    public function testNotice(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->notice('you noticing me');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('NOTICE: you noticing me', $output);
    }

    public function testLogHigherLevel(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::EMERGENCY);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->notice('you noticing me');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringNotContainsString('NOTICE: you noticing me', $output);
    }

    public function testLogWithContext(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->log(LogLevelCode::ALERT, 'you noticing me {output}', ['output' => 'test']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('you noticing me test', $output);
    }

    public function testLogWithException(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->log(
            LogLevelCode::ALERT,
            'you noticing me {output}',
            ['exception' => new \Exception('This is a test')]
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('This is a test', $output);
    }

    public function testLogWithStringLevelAlert(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->log(LogLevel::ALERT, 'you noticing me {output}', ['output' => 'test']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('you noticing me test', $output);
    }

    public function testLogWithStringLevelCritical(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->log(LogLevel::CRITICAL, 'you noticing me {output}', ['output' => 'test']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('you noticing me test', $output);
    }

    public function testLogWithStringLevelError(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->log(LogLevel::ERROR, 'you noticing me {output}', ['output' => 'test']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('you noticing me test', $output);
    }

    public function testLogWithStringLevelWarning(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->log(LogLevel::WARNING, 'you noticing me {output}', ['output' => 'test']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('you noticing me test', $output);
    }

    public function testLogWithStringLevelNotice(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->log(LogLevel::NOTICE, 'you noticing me {output}', ['output' => 'test']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('you noticing me test', $output);
    }

    public function testLogWithStringLevelInfo(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::DEBUG);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->log(LogLevel::INFO, 'you noticing me {output}', ['output' => 'test']);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('you noticing me test', $output);
    }

    public function testRawLogHigherLevel(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(LogLevel::EMERGENCY);
        $logger = new Logger($config, Main::RUN_AS_CLI);
        $logger->rawLog(LogLevelCode::ALERT, 'you noticing me');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringNotContainsString('NOTICE: you noticing me', $output);
    }
}
