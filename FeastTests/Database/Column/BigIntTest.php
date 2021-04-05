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

namespace Database\Column;

use Feast\Database\Column\BigInt;
use PHPUnit\Framework\TestCase;

class BigIntTest extends TestCase
{

    public function testGetUnsignedTrue(): void
    {
        $column = new BigInt('Test', unsigned: true);
        $this->assertTrue($column->isUnsigned());
    }

    public function testGetUnsignedFalse(): void
    {
        $column = new BigInt('Test');
        $this->assertFalse($column->isUnsigned());
    }

    public function testGetType(): void
    {
        $column = new BigInt('Test');
        $this->assertEquals('bigint', $column->getType());
    }

    public function testGetNullable(): void
    {
        $column = new BigInt('Test');
        $this->assertFalse($column->isNullable());
    }

    public function testGetUnsignedText(): void
    {
        $column = new BigInt('Test', unsigned: true);
        $this->assertEquals(' UNSIGNED ', $column->getUnsignedText());
    }

    public function testGetUnsignedTextEmpty(): void
    {
        $column = new BigInt('Test');
        $this->assertEquals('', $column->getUnsignedText());
    }

    public function testGetName(): void
    {
        $column = new BigInt('Test');
        $this->assertEquals('Test', $column->getName());
    }

    public function testGetLength(): void
    {
        $column = new BigInt('Test', 20);
        $this->assertEquals(20, $column->getLength());
    }

}
