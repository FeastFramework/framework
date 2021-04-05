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

namespace Attributes;

use Feast\Attributes\Param;
use Feast\Enums\ParamType;
use Feast\Terminal;
use PHPUnit\Framework\TestCase;

class ParamTest extends TestCase
{
    public function testGetParamText(): void
    {
        $param = new Param('string', 'test', 'Testing the attribute');
        $this->assertEquals('{test} string       Testing the attribute', $param->getParamText(new Terminal(false)));
    }

    public function testGetParamTextFlag(): void
    {
        $param = new Param('string', 'test', 'Testing the attribute', ParamType::FLAG);
        $this->assertEquals('--test=string       Testing the attribute', $param->getParamText(new Terminal(false)));
    }
}
