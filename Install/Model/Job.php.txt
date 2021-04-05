<?php

declare(strict_types=1);

namespace Model;

use Feast\BaseModel;
use Mapper\JobMapper;

class Job extends BaseModel
{
    protected const MAPPER_NAME = JobMapper::class;

    public string $job_id;
    public string $job_name;
    public string $job_context;
    public \Feast\Date $created_at;
    public ?\Feast\Date $ran_at;
    public string $status;
    public int $tries;
    public int $max_tries;
    public string $queue_name;
}