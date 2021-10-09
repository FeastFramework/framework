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

namespace Collection;

use Feast\Collection\Set;
use Feast\Exception\InvalidArgumentException;
use Feast\Exception\InvalidOptionException;
use Feast\Exception\ServerFailureException;
use PHPUnit\Framework\TestCase;
use stdClass;

class SetTest extends TestCase
{
    public function testCreateStringValid(): void
    {
        $set = new Set('string', ['test']);
        $this->assertTrue($set instanceof Set);
    }

    public function testCreateStringInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Set('string', [1]);
    }

    public function testGetType(): void
    {
        $set = new Set('string');
        $this->assertEquals('string', $set->getType());
    }

    public function testOffsetExistsTrue(): void
    {
        $set = new Set('string', ['test']);
        $this->assertTrue($set->offsetExists(0));
    }

    public function testOffsetExistsFalse(): void
    {
        $set = new Set('string', ['test']);
        $this->assertFalse($set->offsetExists(1));
    }

    public function testOffsetGetValid(): void
    {
        $set = new Set('string', ['test', 'test2']);
        $this->assertEquals('test2', $set->offsetGet(1));
    }

    public function testOffsetGetWithPreSorted(): void
    {
        $set = new Set('string', ['test', 'test2'], preValidated: true);
        $this->assertEquals('test2', $set->offsetGet(1));
    }

    public function testOffsetGetInvalid(): void
    {
        $set = new Set('string', ['test']);
        $this->assertEquals(null, $set->offsetGet(1));
    }

    public function testOffsetSetException(): void
    {
        $set = new Set('string', ['test']);
        $this->expectException(InvalidOptionException::class);
        $set->offsetSet(0, 'test');
    }

    public function testOffsetUnsetException(): void
    {
        $set = new Set('string', ['test']);
        $this->expectException(InvalidOptionException::class);
        $set->offsetUnset(0);
    }

    public function testCount(): void
    {
        $set = new Set('string', ['test', 'test2']);
        $this->assertEquals(2, $set->count());
    }

    public function testValidMerge(): void
    {
        $set1 = new Set('string', ['test', 'test2']);
        $set2 = new Set('string', ['testing', 'test2']);
        $set1->merge($set2);
        $this->assertEquals(['test', 'test2', 'testing'], $set1->toArray());
    }

    public function testInvalidMerge(): void
    {
        $set1 = new Set('string', ['test', 'test2']);
        $set2 = new Set('int', [1]);
        $this->expectException(InvalidArgumentException::class);
        $set1->merge($set2);
    }

    public function testMinInvalid(): void
    {
        $set = new Set('string');
        $this->expectException(InvalidOptionException::class);
        $set->min();
    }

    public function testMinInt(): void
    {
        $set = new Set('int', [1, 2, 7]);
        $this->assertEquals(1, $set->min());
    }

    public function testMinFloat(): void
    {
        $set = new Set('float', [1.0, 2.0, 7.0]);
        $this->assertEquals(1.0, $set->min());
    }

    public function testMinObject(): void
    {
        $first = new stdClass();
        $first->item = 1;
        $second = new stdClass();
        $second->item = 2;
        $third = new stdClass();
        $third->item = 7;

        $set = new Set(stdClass::class, [$first, $second, $third]);
        $this->assertEquals(1, $set->min('item'));
    }

    public function testMinEmpty(): void
    {
        $set = new Set('int', []);
        $this->assertEquals(0, $set->min());
    }

    public function testMinObjectEmpty(): void
    {
        $set = new Set(stdClass::class, []);
        $this->assertEquals(0, $set->min('item'));
    }

    public function testMaxInvalid(): void
    {
        $set = new Set('string');
        $this->expectException(InvalidOptionException::class);
        $set->max();
    }

    public function testMaxInt(): void
    {
        $set = new Set('int', [1, 2, 7]);
        $this->assertEquals(7, $set->max());
    }

    public function testMaxFloat(): void
    {
        $set = new Set('float', [1.0, 2.0, 7.0]);
        $this->assertEquals(7.0, $set->max());
    }

    public function testMaxObject(): void
    {
        $first = new stdClass();
        $first->item = 1;
        $second = new stdClass();
        $second->item = 2;
        $third = new stdClass();
        $third->item = 7;

        $set = new Set(stdClass::class, [$first, $second, $third]);
        $this->assertEquals(7, $set->max('item'));
    }

    public function testMaxEmpty(): void
    {
        $set = new Set('int', []);
        $this->assertEquals(0, $set->max());
    }

    public function testMaxObjectEmpty(): void
    {
        $set = new Set(stdClass::class, []);
        $this->assertEquals(0, $set->max('item'));
    }

    public function testAverageInt(): void
    {
        $set = new Set('int', [1, 2, 3]);
        $this->assertEquals(2, $set->average());
    }

    public function testAverageEmpty(): void
    {
        $set = new Set('int', []);
        $this->assertEquals(0, $set->average());
    }

    public function testSumInt(): void
    {
        $set = new Set('int', [1, 2, 7]);
        $this->assertEquals(10, $set->sum());
    }

    public function testSumInvalid(): void
    {
        $set = new Set('string', ['test']);
        $this->expectException(InvalidOptionException::class);

        $set->sum();
    }

    public function testSumEmpty(): void
    {
        $set = new Set('int', []);
        $this->assertEquals(0, $set->sum());
    }

    public function testSumObject(): void
    {
        $first = new stdClass();
        $first->item = 1;
        $second = new stdClass();
        $second->item = 2;
        $third = new stdClass();
        $third->item = 7;

        $set = new Set(stdClass::class, [$first, $second, $third]);
        $this->assertEquals(10, $set->sum('item'));
    }

    /**
     * @throws InvalidOptionException
     */
    public function testSumObjectEmpty(): void
    {
        $set = new Set(stdClass::class);
        $this->assertEquals(0, $set->sum('item'));
    }

    public function testProductInt(): void
    {
        $set = new Set('int', [3, 2, 7]);
        $this->assertEquals(42, $set->product());
    }

    public function testProductInvalid(): void
    {
        $set = new Set('string', ['test']);
        $this->expectException(InvalidOptionException::class);

        $set->product();
    }

    public function testProductEmpty(): void
    {
        $set = new Set('int', []);
        $this->assertEquals(1, $set->product());
    }

    public function testProductObject(): void
    {
        $first = new stdClass();
        $first->item = 3;
        $second = new stdClass();
        $second->item = 2;
        $third = new stdClass();
        $third->item = 7;

        $set = new Set(stdClass::class, [$first, $second, $third]);
        $this->assertEquals(42, $set->product('item'));
    }

    /**
     * @throws InvalidOptionException
     */
    public function testProductObjectEmpty(): void
    {
        $set = new Set(stdClass::class);
        $this->assertEquals(1, $set->product('item'));
    }

    public function testMathObjectOnNonObject(): void
    {
        $set = new Set('int');
        $this->expectException(InvalidOptionException::class);

        $set->sum('item');
    }

    /**
     * @throws InvalidOptionException
     * @throws ServerFailureException
     */
    public function testImplodeNonObject(): void
    {
        $set = new Set('string');
        $set->addAll(['test', 'feast', 'jeremy']);
        $result = $set->implode(',');

        $this->assertEquals('test,feast,jeremy', $result);
    }

    /**
     * @throws InvalidOptionException
     * @throws ServerFailureException
     */
    public function testImplodeObject(): void
    {
        $set = new Set(stdClass::class);
        $item1 = new stdClass();
        $item1->name = 'test';
        $item2 = new stdClass();
        $item2->name = 'feast';
        $item3 = new stdClass();
        $item3->name = 'jeremy';

        $set->addAll([$item1, $item2, $item3]);
        $result = $set->implode(',', 'name');

        $this->assertEquals('test,feast,jeremy', $result);
    }

    /**
     * @throws InvalidOptionException
     * @throws ServerFailureException
     */
    public function testImplodeNonObjectAsObject(): void
    {
        $set = new Set('string');
        $set->addAll(['test', 'feast', 'jeremy']);
        $this->expectException(InvalidOptionException::class);
        $set->implode(',', 'name');
    }

    /**
     * @throws InvalidOptionException
     * @throws ServerFailureException
     */
    public function testImplodeObjectAsNonObject(): void
    {
        $set = new Set(stdClass::class);
        $item1 = new stdClass();
        $item1->name = 'test';
        $item2 = new stdClass();
        $item2->name = 'feast';
        $item3 = new stdClass();
        $item3->name = 'jeremy';

        $set->addAll([$item1, $item2, $item3]);
        $this->expectException(InvalidOptionException::class);
        $set->implode(',');
    }

    public function testMap(): void
    {
        $set = new Set(stdClass::class);
        $item1 = new stdClass();
        $item1->name = 'test';
        $item2 = new stdClass();
        $item2->name = 'feast';
        $item3 = new stdClass();
        $item3->name = 'jeremy';
        $item4 = new stdClass();
        $item4->name = 'fast';

        $set->addAll([$item1, $item2, $item3, $item4]);
        $mapped = $set->map(function (stdClass $item) {
            return str_ends_with($item->name, 't');
        });
        $this->assertEquals([true, true, false, true], $mapped);
    }

    public function testMapWithExtraArrays(): void
    {
        $set = new Set(stdClass::class);
        $item1 = new stdClass();
        $item1->name = 'test';
        $item2 = new stdClass();
        $item2->name = 'feast';
        $item3 = new stdClass();
        $item3->name = 'jeremy';
        $item4 = new stdClass();
        $item4->name = 'fast';

        $set->addAll([$item1, $item2, $item3, $item4]);
        $mapped = $set->map(function (stdClass $item, $second) {
            
            return $second === 'test' && str_ends_with($item->name, 't');
        },['test','false','test','test']);
        $this->assertEquals([true, false, false, true], $mapped);
    }

    public function testWalk(): void
    {
        $set = new Set(stdClass::class);
        $item1 = new stdClass();
        $item1->name = 'test';
        $item2 = new stdClass();
        $item2->name = 'feast';
        $item3 = new stdClass();
        $item3->name = 'jeremy';

        $set->addAll([$item1, $item2, $item3]);
        $result = $set->walk(function ($item, $key, $prefix) {
            $item->name = $prefix . $item->name;
        }, 'a');
        $this->assertEquals('atest', $set[0]->name);
        $this->assertEquals('afeast', $set[1]->name);
        $this->assertEquals('ajeremy', $set[2]->name);
    }

    public function testFilter(): void
    {
        $set = new Set(stdClass::class);
        $item1 = new stdClass();
        $item1->name = 'test';
        $item2 = new stdClass();
        $item2->name = 'feast';
        $item3 = new stdClass();
        $item3->name = 'jeremy';

        $set->addAll([$item1, $item2, $item3]);
        $result = $set->filter(function($item) {
            return str_ends_with($item->name,'t');
        });
        $this->assertCount(3,$set->getValues());
        $this->assertCount(2,$result);
    }

    public function testFilterWithUpdate(): void
    {
        $set = new Set(stdClass::class);
        $item1 = new stdClass();
        $item1->name = 'test';
        $item2 = new stdClass();
        $item2->name = 'feast';
        $item3 = new stdClass();
        $item3->name = 'jeremy';

        $set->addAll([$item1, $item2, $item3]);
        $result = $set->filter(function($item) {
            return str_ends_with($item->name,'t');
        },0,true);
        $this->assertCount(2,$set->getValues());
        $this->assertCount(2,$result);
    }

    public function testReduce(): void
    {
        $set = new Set('int',[1,3,5,6,7,8,10],true,true);
        
        $result = $set->reduce(function($initial,$item) {
            return $item*$initial;
        },1);
        $this->assertEquals(50400,$result);
    }
}
