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

use Feast\Database\Migration;

/**
 * Class MigrationMock extends Migration.
 * This class is solely to be used to test functions in the Database class.
 * DO NOT USE IN PRODUCTION AS NO QUERIES WILL BE EXECUTED!
 */
class MigrationMockNoConnection extends Migration
{
    public const CONNECTION = null;
    protected const NAME = 'testMigration';
}
