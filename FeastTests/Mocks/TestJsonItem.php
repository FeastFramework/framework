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

namespace Mocks;

use Feast\Attributes\JsonItem;
use Feast\Collection\Collection;
use Feast\Collection\Set;
use Feast\Date;

/**
 * Class TestJsonItem
 *
 * @psalm-suppress all
 * @package Mocks
 */
class TestJsonItem
{
    #[JsonItem(name: 'first_name')]
    public string $firstName;
    #[JsonItem(name: 'last_name')]
    public string $lastName;
    #[JsonItem(name: 'test_item')]
    public TestJsonItem $item;

    #[JsonItem(name: 'second_item')]
    public SecondItem $secondItem;

    #[JsonItem(arrayOrCollectionType: TestJsonItem::class)]
    public array $items;

    public array $cards;

    #[JsonItem(arrayOrCollectionType: TestJsonItem::class)]
    public Collection $otherItems;

    #[JsonItem(arrayOrCollectionType: 'string')]
    public Collection $thirdItems;

    #[JsonItem(arrayOrCollectionType: TestJsonItem::class)]
    public Set $otherSet;

    #[JsonItem(arrayOrCollectionType: 'string')]
    public Set $thirdSet;

    public ?int $calls = null;
    public int $count;

    public int $records;

    #[JsonItem(dateFormat: 'Ymd')]
    public Date $timestamp;

    public Date $otherTimestamp;

    public function __construct()
    {
    }
}
