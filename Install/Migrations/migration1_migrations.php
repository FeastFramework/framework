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

namespace Migrations;

use Feast\Database\Migration;

class migration1_migrations extends Migration
{

    protected const NAME = 'Migrations';

    public function up(): void
    {
        $this->connection->rawQuery(
            '
		create table IF NOT EXISTS migrations (
			primary_id int unsigned AUTO_INCREMENT PRIMARY KEY,
			migration_id varchar(255),
			name varchar(255),
			last_up datetime default null,
			last_down datetime default null,
			status enum(\'up\',\'down\') default null,
			INDEX (migration_id))
		'
        );
        parent::up();
    }

    public function down(): void
    {
        $this->connection->rawQuery('DROP TABLE IF EXISTS migrations;');
        parent::down();
    }
}