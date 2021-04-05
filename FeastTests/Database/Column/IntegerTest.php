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

use Feast\Database\Column\Integer;
use Feast\Exception\DatabaseException;
use PHPUnit\Framework\TestCase;

class IntegerTest extends TestCase
{

    public function testInvalidLength(): void
    {
        $this->expectException(DatabaseException::class);
        new Integer('Test', 0);
    }

    public function testGetUnsignedTrue(): void
    {
        $column = new Integer('Test', unsigned: true);
        $this->assertTrue($column->isUnsigned());
    }

    public function testGetUnsignedFalse(): void
    {
        $column = new Integer('Test', unsigned: false);
        $this->assertFalse($column->isUnsigned());
    }

    public function testGetType(): void
    {
        $column = new Integer('Test', unsigned: true);
        $this->assertEquals('int', $column->getType());
    }

    public function testGetNullable(): void
    {
        $column = new Integer('Test', unsigned: true);
        $this->assertFalse($column->isNullable());
    }

    public function testGetUnsignedText(): void
    {
        $column = new Integer('Test', unsigned: true);
        $this->assertEquals(' UNSIGNED ', $column->getUnsignedText());
    }

    public function testGetUnsignedTextEmpty(): void
    {
        $column = new Integer('Test', unsigned: false);
        $this->assertEquals('', $column->getUnsignedText());
    }

    public function testGetName(): void
    {
        $column = new Integer('Test', unsigned: true);
        $this->assertEquals('Test', $column->getName());
    }

    public function testGetLength(): void
    {
        $column = new Integer('Test', 11, unsigned: true);
        $this->assertEquals(11, $column->getLength());
    }

}
