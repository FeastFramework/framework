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

use Feast\Exception\ServerFailureException;
use Feast\Json;
use Mocks\TestJsonItem;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    
    public function setUp(): void
    {
        date_default_timezone_set('America/New_York');
    }

    /**
     * @throws ReflectionException
     * @throws ServerFailureException
     */
    
    public function testMarshal(): void
    {
        $item = new TestJsonItem();
        $item->firstName = 'FEAST';
        $item->lastName = 'Framework';
        $item->count = 4;
        $item->notIncluded = 'Testing';

        $item->item = new TestJsonItem();
        $item->item->firstName = 'Jeremy';
        $item->item->lastName = 'Presutti';
        $item->item->calls = 4;
        $item->cards = ['4', 5, ['6']];
        $item2 = new TestJsonItem();
        $item2->firstName = 'PHP';
        $item2->lastName = '7.4';
        $item3 = new TestJsonItem();
        $item3->firstName = 'PHP';
        $item3->lastName = '8.0';
        $item->items[] = $item2;
        $item->items[] = $item3;

        $item2 = new TestJsonItem();
        $item2->firstName = 'Json';
        $item2->lastName = 'Serializer';
        $item3 = new TestJsonItem();
        $item3->firstName = 'Item';
        $item3->lastName = 'Parsing';

        $item->otherItems = new \Feast\Collection\CollectionList(
            TestJsonItem::class,
            [
                'first' => $item2,
                'second' => $item3,
            ], true
        );

        $item->otherSet = new \Feast\Collection\Set(
            TestJsonItem::class,
            [
                $item2,
                $item3
            ], preValidated: true
        );
        $secondItem = new \Mocks\SecondItem();
        $secondItem->firstName = 'Orlando';
        $secondItem->lastName = 'Florida';

        $item->secondItem = $secondItem;

        $item->thirdItems = new \Feast\Collection\CollectionList(
            'string', [
            'test' => 'theTest',
            'test2' => 'theTest2'
        ], preValidated: true
        );

        $item->thirdSet = new \Feast\Collection\Set(
            'string', [
            'theTest',
            'theTest2'
        ], preValidated: true
        );

        $timestampOne = \Feast\Date::createFromTimestamp(1618534584);
        $timestampTwo = \Feast\Date::createFromTimestamp(1617619260);

        $item->timestamp = $timestampOne;
        $item->otherTimestamp = $timestampTwo;
        
        $item->thirdTimestamp = DateTime::createFromFormat('U','1618534584');
        $item->fourthTimestamp = DateTime::createFromFormat('U','1617619260');
        $item->thirdTimestamp->setTimezone(new DateTimeZone('America/New_York'));
        $item->fourthTimestamp->setTimezone(new DateTimeZone('America/New_York'));

        $data = Json::marshal($item);
        $this->assertEquals(
            '{"first_name":"FEAST","last_name":"Framework","test_item":{"first_name":"Jeremy","last_name":"Presutti","calls":4},"second_item":{"also_first_name":"Orlando","also_last_name":"Florida"},"items":[{"first_name":"PHP","last_name":"7.4","calls":null},{"first_name":"PHP","last_name":"8.0","calls":null}],"cards":["4",5,["6"]],"otherItems":{"first":{"first_name":"Json","last_name":"Serializer","calls":null},"second":{"first_name":"Item","last_name":"Parsing","calls":null}},"thirdItems":{"test":"theTest","test2":"theTest2"},"otherSet":[{"first_name":"Json","last_name":"Serializer","calls":null},{"first_name":"Item","last_name":"Parsing","calls":null}],"thirdSet":["theTest","theTest2"],"calls":null,"count":4,"timestamp":"20210415","otherTimestamp":"2021-04-05T06:41:00-0400","thirdTimestamp":"2021-04-15T20:56:24-0400","fourthTimestamp":"20210405"}',
            $data
        );
    }

    public function testUnmarshalInvalidConstructor(): void
    {
        $this->expectException(\Feast\Exception\ServerFailureException::class);
        Json::unmarshal('{"test":"test"}', \Mocks\BadJsonItem::class);
    }

    public function testUnmarshalWithObject(): void
    {
        $item = new \Mocks\BadJsonItem('ShouldNotExplode');
        $result = Json::unmarshal('{"first_name":"FEAST","last_name":"Framework"}', $item);
        $this->assertEquals('FEAST', $result->firstName);
        $this->assertEquals('Framework', $result->lastName);
    }

    public function testUnmarshal(): void
    {
        $data = '{"first_name":"FEAST","last_name":"Framework","test_item":{"first_name":"Jeremy","last_name":"Presutti","calls":4},"second_item":{"also_first_name":"Orlando","also_last_name":"Florida"},"items":[{"first_name":"PHP","last_name":"7.4","calls":null},{"first_name":"PHP","last_name":"8.0","calls":null}],"cards":["4",5,["6"]],"otherItems":{"first":{"first_name":"Json","last_name":"Serializer","calls":null},"second":{"first_name":"Item","last_name":"Parsing","calls":null}},"thirdItems":{"test":"theTest","test2":"theTest2"},"otherSet":[{"first_name":"Json","last_name":"Serializer","calls":null},{"first_name":"Item","last_name":"Parsing","calls":null}],"thirdSet":["theTest","theTest2"],"calls":null,"count":4,"timestamp":"20210415","otherTimestamp":"2021-04-05T06:41:00-0400","thirdTimestamp":"2021-04-15T20:56:24-0400","fourthTimestamp":"20210405"}';
        /** @var TestJsonItem $result */
        $result = Json::unmarshal($data, TestJsonItem::class);
        $this->assertEquals('FEAST', $result->firstName);
        $this->assertEquals('Framework', $result->lastName);
        $this->assertNull($result->calls);
        $this->assertEquals('Json', $result->otherItems->toArray()['first']->firstName);
        $this->assertEquals('Orlando', $result->secondItem->firstName);
        $this->assertEquals('Florida', $result->secondItem->lastName);
        $this->assertEquals(['test' => 'theTest', 'test2' => 'theTest2'], $result->thirdItems->toArray());
        $this->assertEquals(['theTest', 'theTest2'], $result->thirdSet->toArray());
        $this->assertEquals(['4', 5, ['6']], $result->cards);
        $result->timestamp->setTimezone('America/New_York');
        $result->otherTimestamp->setTimezone('America/New_York');
        $this->assertEquals('20210415', $result->timestamp->getFormattedDate('Ymd'));
        $this->assertEquals('20210405', $result->otherTimestamp->getFormattedDate('Ymd'));
        $this->assertTrue($result->secondItem instanceof \Mocks\SecondItem);
    }

    public function testUnmarshalMarshal(): void
    {
        $data = '{"first_name":"FEAST","last_name":"Framework","test_item":{"first_name":"Jeremy","last_name":"Presutti","calls":4},"second_item":{"also_first_name":"Orlando","also_last_name":"Florida"},"items":[{"first_name":"PHP","last_name":"7.4","calls":null},{"first_name":"PHP","last_name":"8.0","calls":null}],"cards":["4",5,["6"]],"otherItems":{"first":{"first_name":"Json","last_name":"Serializer","calls":null},"second":{"first_name":"Item","last_name":"Parsing","calls":null}},"thirdItems":{"test":"theTest","test2":"theTest2"},"otherSet":[{"first_name":"Json","last_name":"Serializer","calls":null},{"first_name":"Item","last_name":"Parsing","calls":null}],"thirdSet":["theTest","theTest2"],"calls":null,"count":4,"timestamp":"20210415","otherTimestamp":"2021-04-05T06:41:00-0400","thirdTimestamp":"2021-04-15T20:56:24-0400","fourthTimestamp":"20210405"}';
        $this->assertEquals($data, Json::marshal(Json::unmarshal($data, TestJsonItem::class)));
    }

    public function testUnmarshalMarshalUnmarshalMarshal(): void
    {
        $data = '{"first_name":"FEAST","last_name":"Framework","test_item":{"first_name":"Jeremy","last_name":"Presutti","calls":4},"second_item":{"also_first_name":"Orlando","also_last_name":"Florida"},"items":[{"first_name":"PHP","last_name":"7.4","calls":null},{"first_name":"PHP","last_name":"8.0","calls":null}],"cards":["4",5,["6"]],"otherItems":{"first":{"first_name":"Json","last_name":"Serializer","calls":null},"second":{"first_name":"Item","last_name":"Parsing","calls":null}},"thirdItems":{"test":"theTest","test2":"theTest2"},"otherSet":[{"first_name":"Json","last_name":"Serializer","calls":null},{"first_name":"Item","last_name":"Parsing","calls":null}],"thirdSet":["theTest","theTest2"],"calls":null,"count":4,"timestamp":"20210415","otherTimestamp":"2021-04-05T06:41:00-0400","thirdTimestamp":"2021-04-15T20:56:24-0400","fourthTimestamp":"20210405"}';
        $this->assertEquals(
            $data,
            Json::marshal(
                Json::unmarshal(
                    Json::marshal(Json::unmarshal($data, TestJsonItem::class)),
                    TestJsonItem::class
                )
            )
        );
    }
}
