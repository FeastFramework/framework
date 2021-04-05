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

use Feast\Collection\CollectionList;
use Feast\Enums\CollectionSort;
use Feast\Exception\InvalidArgumentException;
use Feast\Exception\InvalidOptionException;
use PHPUnit\Framework\TestCase;
use stdClass;

class CollectionListTest extends TestCase
{
    public function testCreateStringValid(): void
    {
        $collection = new CollectionList('string', ['a' => 'test', 'b' => 'testing']);
        $this->assertTrue($collection instanceof CollectionList);
    }

    public function testRemoveByKey(): void
    {
        $collection = new CollectionList('string', ['a' => 'test', 'b' => 'testing']);
        $collection->removeByKey('a');
        $this->assertEquals(['b' => 'testing'], $collection->toArray());
    }

    public function testGet(): void
    {
        $collection = new CollectionList('string', ['a' => 'test', 'b' => 'testing']);
        $item = $collection->get('a');
        $this->assertEquals('test', $item);
    }

    public function testOffsetExistsTrue(): void
    {
        $collection = new CollectionList('string', ['a' => 'test', 'b' => 'testing']);
        $this->assertTrue($collection->offsetExists('a'));
    }

    public function testOffsetExistsFalse(): void
    {
        $collection = new CollectionList('string', ['a' => 'test', 'b' => 'testing']);
        $this->assertFalse($collection->offsetExists('x'));
    }

    public function testOffsetSet(): void
    {
        $collection = new CollectionList('string', ['a' => 'test', 'b' => 'testing']);
        $collection->offsetSet('a', 'production');
        $this->assertEquals('production', $collection->get('a'));
    }

    public function testOffsetUnset(): void
    {
        $collection = new CollectionList('string', ['a' => 'test', 'b' => 'testing']);
        $collection->offsetUnset('a');
        $this->assertEquals(null, $collection->get('a'));
    }

    public function testGetValues(): void
    {
        $collection = new CollectionList('string', ['a' => 'test', 'b' => 'testing']);
        $this->assertEquals(['test', 'testing'], $collection->getValues());
    }

    public function testIsEmptyTrue(): void
    {
        $collection = new CollectionList('string');
        $this->assertTrue($collection->isEmpty());
    }

    public function testIsEmptyFalse(): void
    {
        $collection = new CollectionList('string', ['a' => 'test', 'b' => 'testing']);
        $this->assertFalse($collection->isEmpty());
    }

    public function testSize(): void
    {
        $collection = new CollectionList('string', ['a' => 'test', 'b' => 'testing']);
        $this->assertEquals(2, $collection->size());
    }

    public function testSortKeyAndShift(): void
    {
        $collection = new CollectionList('string', ['b' => 'test', 'a' => 'testing']);
        $collection->sort(CollectionSort::KEY, true);
        $item = $collection->shift();
        $this->assertEquals('testing', $item);
    }

    public function testReverseSortKeyAndPop(): void
    {
        $collection = new CollectionList('string', ['b' => 'test', 'a' => 'testing']);
        $collection->sort(CollectionSort::KEY_REVERSE, true);
        $item = $collection->pop();
        $this->assertEquals('testing', $item);
    }

    public function testSortValueAndShift(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing']);
        $collection->sort(CollectionSort::VALUE, true);
        $item = $collection->shift();
        $this->assertEquals('early', $item);
    }

    public function testSortValueAndShiftWithPresorted(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing'], true);
        $collection->sort(CollectionSort::VALUE, true);
        $item = $collection->shift();
        $this->assertEquals('early', $item);
    }

    public function testReverseSortValueAndPop(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing']);
        $collection->sort(CollectionSort::VALUE_REVERSE, true);
        $item = $collection->pop();
        $this->assertEquals('early', $item);
    }

    public function testSortInvalid(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing']);
        $this->expectException(InvalidOptionException::class);
        $collection->sort(7, true);
    }

    public function testShuffle(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing']);
        $this->assertTrue(is_array($collection->shuffle(true)));
    }

    public function testClear(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing']);
        $collection->clear();
        $this->assertEmpty($collection->toArray());
    }

    public function testFirst(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing']);
        $this->assertEquals('early', $collection->first());
    }

    public function testIndexOf(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing']);
        $this->assertEquals('a', $collection->indexOf('testing'));
    }

    public function testLastIndexOf(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing']);
        $this->assertEquals('x', $collection->lastIndexOf('testing'));
    }

    public function testIndexOfNotFound(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing']);
        $this->assertEquals(null, $collection->indexOf('testing2'));
    }

    public function testLastIndexOfNotFound(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing']);
        $this->assertEquals(null, $collection->lastIndexOf('testing2'));
    }

    public function testContainsTrue(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing']);
        $this->assertTrue($collection->contains('early'));
    }

    public function testContainsFalse(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing']);
        $this->assertFalse($collection->contains('late'));
    }

    public function testContainsAllTrue(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing2']);
        $this->assertTrue($collection->containsAll(['early', 'testing']));
    }

    public function testContainsAllFalse(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing2']);
        $this->assertFalse($collection->containsAll(['late', 'testing2']));
    }

    public function testRemove(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing2']);
        $collection->remove('early');
        $this->assertEquals(2, $collection->count());
    }

    public function testRemoveAll(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing2']);
        $collection->removeAll(['early', 'testing']);
        $this->assertEquals(1, $collection->count());
    }

    public function testObjectSortInvalid(): void
    {
        $collection = new CollectionList('object');
        $this->expectException(InvalidOptionException::class);
        $collection->objectSort('test');
    }

    public function testObjectSortTypeInvalid(): void
    {
        $collection = new CollectionList(stdClass::class);
        $this->expectException(InvalidOptionException::class);
        $collection->objectSort('test', 7);
    }

    public function testSortObjectValueAndPop(): void
    {
        $first = new stdClass();
        $first->item = 'b';
        $second = new stdClass();
        $second->item = 'a';
        $third = new stdClass();
        $third->item = 'c';
        $collection = new CollectionList(stdClass::class, [$first, $second, $third]);

        $collection->objectSort('item', modifyOriginal: true);
        $this->assertEquals('a', $collection->first()->item);
    }

    public function testSortObjectValueArrayAndPop(): void
    {
        $first = new stdClass();
        $first->item = 'b';
        $first->name = 'c';
        $second = new stdClass();
        $second->item = 'b';
        $second->name = 'a';
        $third = new stdClass();
        $third->item = 'c';
        $third->name = 'x';
        $collection = new CollectionList(stdClass::class, [$first, $second, $third]);

        $collection->objectSort(['item', 'name'], modifyOriginal: true);
        $this->assertEquals('a', $collection->first()->name);
    }

    public function testSortObjectValueReverseAndPop(): void
    {
        $first = new stdClass();
        $first->item = 'b';
        $second = new stdClass();
        $second->item = 'a';
        $third = new stdClass();
        $third->item = 'c';
        $collection = new CollectionList(stdClass::class, [$first, $second, $third]);

        $collection->objectSort('item', CollectionSort::VALUE_REVERSE, true);
        $this->assertEquals('c', $collection->first()->item);
    }

    public function testCreateStringInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CollectionList('string', [1]);
    }

    public function testCreateIntInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CollectionList('int', [1.0]);
    }

    public function testCreateFloatInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CollectionList('float', [1]);
    }

    public function testCreateIterableInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CollectionList('iterable', [1]);
    }

    public function testCreateArrayInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CollectionList('array', [1]);
    }

    public function testCreateObjectInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CollectionList('object', [1]);
    }

    public function testCreateBoolInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CollectionList('bool', [1]);
    }

    public function testCreateCallableInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CollectionList('callable', [1]);
    }

    public function testCreateStdClassInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CollectionList(stdClass::class, [1]);
    }

    public function testCreateIntValid(): void
    {
        $collection = new CollectionList('int', [1]);
        $this->assertTrue($collection instanceof CollectionList);
    }

    public function testCreateFloatValid(): void
    {
        $collection = new CollectionList('float', [1.0]);
        $this->assertTrue($collection instanceof CollectionList);
    }

    public function testCreateIterableValid(): void
    {
        $collection = new CollectionList('iterable', [['test']]);
        $this->assertTrue($collection instanceof CollectionList);
    }

    public function testCreateArrayValid(): void
    {
        $collection = new CollectionList('array', [['test']]);
        $this->assertTrue($collection instanceof CollectionList);
    }

    public function testCreateObjectValid(): void
    {
        $collection = new CollectionList('object', [new stdClass()]);
        $this->assertTrue($collection instanceof CollectionList);
    }

    public function testCreateBoolValid(): void
    {
        $collection = new CollectionList('bool', [true, false]);
        $this->assertTrue($collection instanceof CollectionList);
    }

    public function testCreateCallableValid(): void
    {
        $collection = new CollectionList(
            'callable', [
                          function () {
                          }
                      ]
        );
        $this->assertTrue($collection instanceof CollectionList);
    }

    public function testCreateStdClassValid(): void
    {
        $collection = new CollectionList(stdClass::class, [new stdClass()]);
        $this->assertTrue($collection instanceof CollectionList);
    }

    public function testCreateMixedValid(): void
    {
        $collection = new CollectionList(
            'mixed', [
                       new stdClass(),
                       1,
                       2.0,
                       'test',
                       true,
                       function () {
                       },
                       []
                   ]
        );
        $this->assertTrue($collection instanceof CollectionList);
    }

    ##### BELOW TESTS ARE ALL THE ARRAY ACCESSOR BASED FUNCTIONS #####
    public function testRewindAndNextAndCurrentAndKey(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing2']);
        $collection->next();
        $this->assertEquals('testing', $collection->current());
        $this->assertEquals('a', $collection->key());

        $collection->rewind();
        $this->assertEquals('early', $collection->current());
        $this->assertEquals('b', $collection->key());
    }

    public function testValid(): void
    {
        $collection = new CollectionList('string', ['b' => 'early', 'a' => 'testing', 'x' => 'testing2']);
        $this->assertTrue($collection->valid());
        $collection->next();
        $collection->next();
        $collection->next();
        $this->assertFalse($collection->valid());
    }
}
