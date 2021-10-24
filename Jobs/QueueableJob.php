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

namespace Feast\Jobs;

use Feast\Date;
use Feast\Exception\InvalidDateException;
use Feast\Interfaces\JobInterface;
use Mapper\JobMapper;
use Model\Job;

abstract class QueueableJob implements JobInterface
{
    public const JOB_STATUS_PENDING = 'pending';
    public const JOB_STATUS_RUNNING = 'running';
    public const JOB_STATUS_COMPLETE = 'complete';
    public const JOB_STATUS_FAILED = 'failed';

    protected int $maxTries = 3;
    protected string $queueName = 'default';
    protected string $jobName = '';

    /**
     * Store job in the database for the queue to pick up.
     *
     * @param ?JobMapper $jobMapper
     * @return Job
     * @throws InvalidDateException
     * @throws \Exception
     */
    public function store(JobMapper $jobMapper = null): Job
    {
        $jobMapper ??= new JobMapper();
        $model = new Job();
        $model->job_id = $this->generateUuid();
        $model->job_name = $this->jobName;
        $model->job_context = serialize($this);
        $model->created_at = Date::createFromNow();
        $model->ran_at = null;
        $model->status = 'pending';
        $model->tries = 0;
        $model->max_tries = $this->maxTries;
        $model->queue_name = $this->queueName;
        $jobMapper->save($model);
        return $model;
    }

    /**
     * Set the queue name the job should run on.
     *
     * @param string $queueName
     * @return $this
     */
    public function setQueueName(string $queueName): static
    {
        $this->queueName = $queueName;

        return $this;
    }

    /**
     * Set maximum number of tries.
     *
     * @param int $maxTries
     * @return $this
     */
    public function setMaxTries(int $maxTries): static
    {
        $this->maxTries = $maxTries;

        return $this;
    }

    /**
     * Set the job name for this job.
     *
     * @param string $jobName
     * @return $this
     */
    public function setJobName(string $jobName): static
    {
        $this->jobName = $jobName;

        return $this;
    }

    /**
     * Get the name of the job.
     *
     * @return string
     */
    public function getJobName(): string
    {
        return $this->jobName;
    }

    /**
     * Get the maximum number of retries.
     *
     * @return int
     */
    public function getMaxTries(): int
    {
        return $this->maxTries;
    }

    /**
     * Get the name of the queue this job should run on.
     *
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * @throws \Exception
     */
    protected function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
