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

use Feast\Help;
use PHPUnit\Framework\TestCase;

class HelpTest extends TestCase
{
    protected Help $help;

    public function setUp(): void
    {
        $this->help = new Help(new \Feast\Terminal(false), ['famine']);
    }

    public function testPrintCliMethodHelpWithParams(): void
    {
        $expected = '
Usage: php famine feast:create:service {name}

Create a service class from the template file.

Parameters
{name} string       Name of service to create
';
        
        $this->help->printCliMethodHelp('feast:create:service');
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals(str_replace("\r\n","\n", $expected), str_replace("\r\n","\n", $output));
    }

    public function testPrintCliClass(): void
    {
        $this->help->printCliMethodHelp('feast:create');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith(
            'Usage: php famine command options' . PHP_EOL . 'Available feast:create commands',
            trim($output)
        );
    }

    public function testPrintCliClassSingleMethod(): void
    {
        $this->help->printCliMethodHelp('small');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('small' . "\n\n" . 'Usage: php famine small:create', str_replace("\r\n","\n",$output));
    }

    public function testPrintCliMethodHelpWithoutParams(): void
    {
        $expected = '
Usage: php famine feast:cache:config-generate

Clear config cache file (if any) and regenerate.

';
        $this->help->printCliMethodHelp('feast:cache:config-generate');
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals(str_replace("\r\n","\n", $expected), str_replace("\r\n","\n", $output));
    }

    public function testPrintCliMethodNonExistentController(): void
    {
        $this->help->printCliMethodHelp('non-existent:test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Method non-existent:test does not exist!', $output);
    }

    public function testPrintCliClassNonExistentController(): void
    {
        $this->help->printCliMethodHelp('non-existent');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringStartsWith('Class non-existent does not exist!', $output);
    }
}
