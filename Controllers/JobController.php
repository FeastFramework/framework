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

namespace Feast\Controllers;

use Exception;
use Feast\Attributes\Action;
use Feast\Attributes\Param;
use Feast\CliController;
use Feast\Date;
use Feast\Enums\ParamType;
use Feast\Exception\InvalidDateException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\ErrorLoggerInterface;
use Feast\Interfaces\LoggerInterface;
use Feast\Jobs\CronJob;
use Feast\Jobs\QueueableJob;
use Feast\ServiceContainer\NotFoundException;
use Mapper\JobMapper;
use Model\Job;
use Throwable;

class JobController extends CliController
{

    /**
     * @param LoggerInterface $logger
     * @param JobMapper $jobMapper
     * @param ErrorLoggerInterface $errorLogger
     * @param string|null $queues
     * @param bool $keepalive
     * @param positive-int $wait
     * @param bool $exitLoop
     * @return void
     * @throws \Feast\Exception\ServerFailureException
     * @throws \Feast\ServiceContainer\NotFoundException
     */
    #[Action(usage: '--keepalive={true|false} {queues}', description: 'Listen for and run all jobs on one or more queues.')]
    #[Param(type: 'string', name: 'queues', description: 'Name of queues to monitor, pipe delimited')]
    #[Param(type: 'bool', name: 'keepalive', description: 'True to run as a process loop (default: true)', paramType: ParamType::FLAG)]
    #[Param(type: 'int', name: 'wait', description: 'Number of seconds to pause if no jobs found (default: 10)', paramType: ParamType::FLAG)]
    public function listenGet(
        LoggerInterface $logger,
        JobMapper $jobMapper,
        ErrorLoggerInterface $errorLogger,
        ?string $queues = null,
        bool $keepalive = true,
        int $wait = 10,
        bool $exitLoop = false # this param is only used to force loop exit in testing.
    ): void
    {
        if ($queues === null) {
            $this->help('feast:job:listen');
            return;
        }
        $queueList = explode('|', $queues);
        $this->terminal->message('Listening for jobs on the following queues: ' . implode(', ', $queueList));
        do {
            $job = $jobMapper->findOnePendingByQueues($queueList);
            if ($job instanceof Job && !file_exists(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'maintenance.txt')) {
                $this->runJob($job, $errorLogger, $logger, $jobMapper);
            } else {
                $this->terminal->command('No jobs found. ', false);
                if ($keepalive) {
                    $this->terminal->command('Sleeping for ' . (string)$wait . ' seconds.');
                    sleep($wait);
                } else {
                    $this->terminal->command('Exiting.');
                }
            }
        } while ($keepalive && !$exitLoop);
    }

    /**
     * @throws NotFoundException
     * @throws ServerFailureException
     */
    #[Action(usage: '{job}', description: 'Run the specified job. Job will run even if max count exceeded.')]
    #[Param(type: 'string', name: 'job', description: 'Job ID of job to run (uuid)')]
    public function runOneGet(
        LoggerInterface $logger,
        JobMapper $jobMapper,
        ErrorLoggerInterface $errorLogger,
        ?string $job = null,
    ): void {
        if ($job === null) {
            $this->help('feast:job:run-one');
            return;
        }

        $this->terminal->message('Searching for job ' . $job . '.');
        $jobData = $jobMapper->findByPrimaryKey($job);
        if ($jobData === null) {
            $this->terminal->error('Job ' . $job . ' not found.');
            return;
        }
        if ($jobData->status === QueueableJob::JOB_STATUS_RUNNING) {
            $this->terminal->error('Job ' . $job . ' is currently running.');
            return;
        }

        if ($jobData->status === QueueableJob::JOB_STATUS_COMPLETE) {
            $this->terminal->command('Job ' . $job . ' already ran successfully.');
            return;
        }
        $success = $this->runJob($jobData, $errorLogger, $logger, $jobMapper);
        if ($success) {
            $this->terminal->message('Job ' . $job . ' ran successfully.');
        }
    }

    /**
     * @throws InvalidDateException
     */
    #[Action(description: 'Run all cron jobs.')]
    public function runCronGet(
        ?Date $now,
        ConfigInterface $config,
    ): void {
        $now ??= Date::createFromNow();
        $filePath = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'scheduled_jobs.php';
        $privateProcess = $this->shouldRunAsPrivateProcess($config);
        /** @var array<CronJob> $scheduledJobs */
        $scheduledJobs = file_exists($filePath) ? require($filePath) : [];
        foreach ($scheduledJobs as $job) {
            if ($job->shouldRun($now, $config)) {
                $this->runCronJob($job, $privateProcess, $now);
            }
        }
    }

    /**
     * @throws InvalidDateException
     */
    #[Action(usage: '{job}', description: 'Run the specified job.')]
    #[Param(type: 'string', name: 'job', description: 'Job class to run')]
    public function runCronItemGet(
        ?string $job = null,
        ?int $now = null,
        ?int $overlap = null
    ): void {
        if ($job === null) {
            $this->help('feast:job:run-cron-item');
            return;
        }
        $now = $now === null ? Date::createFromNow() : Date::createFromTimestamp($now);
        /** @var CronJob|null $jobProcess */
        $jobProcess = new $job();
        if ($jobProcess instanceof CronJob === false) {
            $this->terminal->error($job . ' is not a CronJob');
            return;
        }
        if ($overlap !== null) {
            $jobProcess->withoutOverlapping($overlap);
        }
        $jobProcess->startRun($now);
        try {
            $jobProcess->run();
        } catch (Exception) {
            // Empty catch
        }
        $jobProcess->stopRun($now);
    }

    /**
     * @throws Exception
     */
    protected function runJob(
        Job $job,
        ErrorLoggerInterface $errorLogger,
        LoggerInterface $logger,
        JobMapper $jobMapper
    ): bool {
        $canRun = $jobMapper->markJobRunningIfAble($job);
        if ($canRun === false) {
            $logger->error('Could not lock job ' . $job->job_id . '.');
            $this->terminal->error('Could not lock job ' . $job->job_id . '.');
            return false;
        }
        /** @var Job $job */
        $job = $jobMapper->findByPrimaryKey($job->job_id);
        /** @var ?QueueableJob $jobData */
        $jobData = unserialize($job->job_context);
        $success = false;
        if ($jobData instanceof QueueableJob) {
            try {
                $success = $jobData->run();
            } catch (Throwable $exception) {
                $errorLogger->exceptionHandler($exception, true);
            }
            $job->status = $success ? QueueableJob::JOB_STATUS_COMPLETE : QueueableJob::JOB_STATUS_PENDING;
            $job->ran_at = Date::createFromNow();
            $job->tries++;
            if ($job->tries >= $job->max_tries && $success === false) {
                $job->status = QueueableJob::JOB_STATUS_FAILED;
            }
            $jobMapper->save($job);

            if ($success === false) {
                $logger->error('Job ' . $job->job_id . ' failed.');
                $this->terminal->error('Job ' . $job->job_id . ' failed.');
            }
        } else {
            $logger->error('Job ' . $job->job_id . ' contains invalid serialized data.');
            $this->terminal->error('Job ' . $job->job_id . ' contains invalid serialized data.');
        }
        return $success;
    }

    protected function runCronJob(CronJob $job, bool $privateProcess, Date $now): void
    {
        if ($privateProcess && $job->isBackgroundable()) {
            $overlap = $job->getOverlapTime();
            $command = $this->getCommandLine($job, $now, $overlap);

            exec($command);
        } else {
            $this->runCronItemGet($job::class, $now->getTimestamp(), $job->getOverlapTime());
        }
    }

    protected function shouldRunAsPrivateProcess(ConfigInterface $config): bool
    {
        return $config->getSetting('cron.spawn', true) &&
            str_starts_with(strtolower(php_uname('s')), 'win') === false;
    }

    protected function getCommandLine(CronJob $job, Date $now, ?int $overlap): string
    {
        $command = 'nohup php famine feast:job:run-cron-item job ' . escapeshellarg(
                $job::class
            ) . ' now ' . escapeshellarg((string)$now->getTimestamp());
        if ($overlap !== null) {
            $command .= ' overlap ' . escapeshellarg((string)$overlap);
        }
        $command .= ' > /dev/null 2>&1 &';
        return $command;
    }
}
