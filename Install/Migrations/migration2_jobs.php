<?php

declare(strict_types=1);

namespace Migrations;

use Feast\Database\Migration;
use Feast\Database\Table\TableFactory;

class migration2_jobs extends Migration
{

    protected const NAME = 'Jobs';

    public function up(): void
    {
        $table = TableFactory::getTable('jobs');
        $table->varChar('job_id')
            ->varChar('job_name')
            ->text('job_context')
            ->timestamp('created_at')
            ->dateTime('ran_at',null,true)
            ->varChar('status', default: 'pending')
            ->tinyInt('tries')
            ->tinyInt('max_tries')
            ->varChar('queue_name');
        $table->create();
        $this->connection->rawQuery('ALTER TABLE jobs add PRIMARY KEY (job_id)');
        parent::up();
    }

    public function down(): void
    {
        /** @todo Create down query */
        TableFactory::getTable('jobs')->drop();
        parent::down();
    }
}
