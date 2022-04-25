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

namespace Migrations;

use Feast\Database\Migration;
use Feast\Database\Table\TableFactory;

class migration1_migrations extends Migration
{

    protected const NAME = 'Migrations';

    public function up(): void
    {
        $table = TableFactory::getTable('migrations');
        $table->autoIncrement('primary_id');
        $table->varChar('migration_id');
        $table->varChar('name');
        $table->dateTime('last_up', nullable: true);
        $table->dateTime('last_down', nullable: true);
        $table->varChar('status');
        $table->create();

        $this->connection->rawQuery('CREATE INDEX migration_migration_id ON migrations (migration_id)');
        parent::up();
    }

    public function down(): void
    {
        $table = TableFactory::getTable('migrations');
        $table->drop();
        parent::down();
    }
}
