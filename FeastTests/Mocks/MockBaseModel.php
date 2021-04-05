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

namespace Mocks;

use Feast\BaseModel;
use Feast\Date;

class MockBaseModel extends BaseModel
{
    protected const MAPPER_NAME = MockBaseMapper::class;

    public null|int|string $id = null;
    public ?Date $theDate = null;
    public ?string $theName = null;
    public ?string $theNull = null;
    public ?\stdClass $theThing = null;
    public ?string $passEncrypted = null;
}
