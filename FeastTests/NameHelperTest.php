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

use Feast\NameHelper;
use PHPUnit\Framework\TestCase;

class NameHelperTest extends TestCase
{

    public function testGetAction(): void
    {
        $action = 'test-run';
        $this->assertEquals('testRunAction', NameHelper::getDefaultAction($action));
    }

    public function testGetName(): void
    {
        $genericName = 'test-run';
        $this->assertEquals('TestRun', NameHelper::getName($genericName));
    }

    public function testGetController(): void
    {
        $controller = 'test-run';
        $this->assertEquals('TestRunController', NameHelper::getController($controller));
    }
}
