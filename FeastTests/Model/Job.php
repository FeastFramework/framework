<?php

declare(strict_types=1);

namespace Model;

use Feast\BaseModel;
use Mapper\JobMapper;

class Job extends BaseModel
{
    protected const MAPPER_NAME = JobMapper::class;

    public string $job_id = '1';
    public string $job_name = '';
    public string $job_context = '';
    public ?\Feast\Date $created_at = null;
    public ?\Feast\Date $ran_at = null;
    public string $status = 'pending';
    public int $tries = 0;
    public int $max_tries = 3;
    public string $queue_name = 'default';
}