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

use Feast\Database\Column\Decimal;
use Feast\Exception\DatabaseException;
use PHPUnit\Framework\TestCase;

class DecimalTest extends TestCase
{

    public function testCreate(): void
    {
        $column = new Decimal('Test');
        $this->assertTrue($column instanceof Decimal);
    }

    public function testCreateInvalidDecimal(): void
    {
        $this->expectException(DatabaseException::class);
        new Decimal('Test', 10, -1);
    }

    public function testGetDecimal(): void
    {
        $column = new Decimal('Test', 10, 4);
        $this->assertEquals(4, $column->getDecimal());
    }

    public function testGetDefault(): void
    {
        $column = new Decimal('Test', 10, 4, default: '10.0');
        $this->assertEquals('10.0', $column->getDefault());
    }

}
