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

use Feast\Terminal;
use PHPUnit\Framework\TestCase;

class TerminalTest extends TestCase
{
    public function testCommandColorOptional(): void
    {
        $terminal = new Terminal();
        $this->assertStringContainsString('test', $terminal->commandText('test'));
    }

    public function testCommandTextColor(): void
    {
        $terminal = new Terminal(true);
        $this->assertStringContainsString('33mtest', $terminal->commandText('test'));
    }

    public function testCommandTextNoColor(): void
    {
        $terminal = new Terminal(false);
        $this->assertEquals('test', $terminal->commandText('test'));
    }

    public function testCommand(): void
    {
        $terminal = new Terminal(false);
        $this->expectOutputString('test' . PHP_EOL);
        $terminal->command('test');
    }

    public function testMessageTextColor(): void
    {
        $terminal = new Terminal(true);
        $this->assertStringContainsString('test', $terminal->messageText('test'));
    }

    public function testMessageTextNoColor(): void
    {
        $terminal = new Terminal(false);
        $this->assertEquals('test', $terminal->errorText('test'));
    }

    public function testError(): void
    {
        $terminal = new Terminal(false);
        $this->expectOutputString('test' . PHP_EOL);
        $terminal->error('test');
    }

    public function testErrorTextColor(): void
    {
        $terminal = new Terminal(true);
        $this->assertStringContainsString('97mtest', $terminal->errorText('test'));
    }

    public function testErrorTextNoColor(): void
    {
        $terminal = new Terminal(false);
        $this->assertEquals('test', $terminal->errorText('test'));
    }

    public function testMessage(): void
    {
        $terminal = new Terminal(false);
        $this->expectOutputString('test' . PHP_EOL);
        $terminal->message('test');
    }

    public function testPrompt(): void
    {
        $terminal = new Terminal(false);
        \Feast\ReadlineMock::$responses = ['FEAST'];
        $this->expectOutputString('What is your name?');
        $result = $terminal->getInputFromPrompt('What is your name?');
        $this->assertEquals('FEAST', $result);
    }

    public function testPromptWithDefault(): void
    {
        $terminal = new Terminal(false);
        \Feast\ReadlineMock::$responses = [''];
        $this->expectOutputString('What is your name?[FEAST]');
        $result = $terminal->getInputFromPrompt('What is your name?', 'FEAST');
        $this->assertEquals('FEAST', $result);
    }

    public function testPromptArrayWithSentinal(): void
    {
        $terminal = new Terminal(false);
        \Feast\ReadlineMock::$responses = ['Food', 'PHP', 'To crush your enemies', 'FEAST', 'Not Feast'];
        $this->expectOutputString('What is best in life?' . PHP_EOL . 'Enter \'FEAST\' when finished.' . PHP_EOL . '[][][][]');
        $result = $terminal->getArrayFromPromptWithSentinel('What is best in life?', 'FEAST');
        $this->assertEquals(
            [
                'Food',
                'PHP',
                'To crush your enemies',
            ],
            $result
        );
    }
    
    public function testPromptArrayWithCount(): void
    {
        $terminal = new Terminal(false);
        \Feast\ReadlineMock::$responses = ['Food', 'PHP', 'To crush your enemies', '', 'FEAST', 'Not Feast'];
        $this->expectOutputString('What is best in life?What else is best in life?What else is best in life?What else is best in life?What else is best in life?');
        $result = $terminal->getArrayFromPromptWithCount('What is best in life?', 4, 'What else is best in life?');
        $this->assertEquals(
            [
                'Food',
                'PHP',
                'To crush your enemies',
                'FEAST'
            ],
            $result
        );

    }
}
