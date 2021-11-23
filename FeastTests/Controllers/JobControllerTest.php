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

namespace Controllers;

use Feast\CliArguments;
use Feast\Config\Config;
use Feast\Controllers\JobController;
use Feast\Date;
use Feast\Interfaces\ErrorLoggerInterface;
use Feast\Interfaces\LoggerInterface;
use Feast\Jobs\QueueableJob;
use Mapper\JobMapper;
use Mocks\MockCronJob;
use Model\Job;
use PHPUnit\Framework\TestCase;

class JobControllerTest extends TestCase
{
    public function setUp(): void
    {
        \Feast\Controllers\FileData::reset();
    }

    public function testRunInvalid(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $controller->listenGet(
            $this->createStub(LoggerInterface::class),
            $this->createStub(JobMapper::class),
            $this->createStub(ErrorLoggerInterface::class)
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString(
            'Usage: php famine feast:job:listen --keepalive={true|false} {queues}',
            $output
        );
    }

    public function testRunNoJobs(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $jobMapper = $this->createStub(JobMapper::class);

        $controller->listenGet(
            $this->createStub(LoggerInterface::class),
            $jobMapper,
            $this->createStub(ErrorLoggerInterface::class),
            'default',
            false
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('No jobs found. Exiting', $output);
    }

    public function testRunNoJobsLoop(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $jobMapper = $this->createStub(JobMapper::class);

        $controller->listenGet(
                      $this->createStub(LoggerInterface::class),
                      $jobMapper,
                      $this->createStub(ErrorLoggerInterface::class),
                      'default',
            exitLoop: true
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('No jobs found. Sleeping for 10 seconds', $output);
    }

    public function testRunWithJobs(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );

        $fakeJob = $this->getMockForAbstractClass(QueueableJob::class);
        $fakeJob->method('run')->willReturn(true);

        $job = new Job();
        $job->job_id = 'default';
        $job->tries = 0;
        $job->max_tries = 3;
        $job->status = QueueableJob::JOB_STATUS_PENDING;
        $job->job_context = serialize($fakeJob);

        $jobMapper = $this->createStub(JobMapper::class);
        $jobMapper->method('findOnePendingByQueues')->willReturn($job);
        $jobMapper->method('findByPrimaryKey')->willReturn($job);
        $jobMapper->method('markJobPendingIfAble')->willReturn(true);

        $controller->listenGet(
            $this->createStub(LoggerInterface::class),
            $jobMapper,
            $this->createStub(ErrorLoggerInterface::class),
            'default',
            false
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('Listening for jobs on the following queues: default', trim($output));
    }

    public function testRunOneInvalid(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $controller->runOneGet(
            $this->createStub(LoggerInterface::class),
            $this->createStub(JobMapper::class),
            $this->createStub(ErrorLoggerInterface::class)
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Usage: php famine feast:job:run-one {job}', $output);
    }

    public function testRunOneNotFound(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $jobMapper = $this->createStub(JobMapper::class);

        $controller->runOneGet(
            $this->createStub(LoggerInterface::class),
            $jobMapper,
            $this->createStub(ErrorLoggerInterface::class),
            'default'
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Searching for job default.', $output);
        $this->assertStringContainsString('Job default not found.', $output);
    }

    public function testRunOneRunning(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $job = new Job();
        $job->status = QueueableJob::JOB_STATUS_RUNNING;

        $jobMapper = $this->createStub(JobMapper::class);
        $jobMapper->method('findByPrimaryKey')->willReturn($job);
        $controller->runOneGet(
            $this->createStub(LoggerInterface::class),
            $jobMapper,
            $this->createStub(ErrorLoggerInterface::class),
            'default'
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Searching for job default.', $output);
        $this->assertStringContainsString('Job default is currently running.', $output);
    }

    public function testRunOneAlreadySuccessful(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $job = new Job();
        $job->status = QueueableJob::JOB_STATUS_COMPLETE;

        $jobMapper = $this->createStub(JobMapper::class);
        $jobMapper->method('findByPrimaryKey')->willReturn($job);
        $controller->runOneGet(
            $this->createStub(LoggerInterface::class),
            $jobMapper,
            $this->createStub(ErrorLoggerInterface::class),
            'default'
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Searching for job default.', $output);
        $this->assertStringContainsString('Job default already ran successfully.', $output);
    }

    public function testRunOneCannotLock(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $job = new Job();
        $job->job_id = 'default';
        $job->status = QueueableJob::JOB_STATUS_PENDING;

        $jobMapper = $this->createStub(JobMapper::class);
        $jobMapper->method('findByPrimaryKey')->willReturn($job);
        $jobMapper->method('markJobPendingIfAble')->willReturn(false);
        $controller->runOneGet(
            $this->createStub(LoggerInterface::class),
            $jobMapper,
            $this->createStub(ErrorLoggerInterface::class),
            'default'
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Searching for job default.', $output);
        $this->assertStringContainsString('Could not lock job default.', $output);
    }

    public function testRunOneSuccess(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $fakeJob = $this->getMockForAbstractClass(QueueableJob::class);
        $fakeJob->method('run')->willReturn(true);

        $job = new Job();
        $job->job_id = 'default';
        $job->tries = 0;
        $job->max_tries = 3;
        $job->status = QueueableJob::JOB_STATUS_PENDING;
        $job->job_context = serialize($fakeJob);

        $jobMapper = $this->createStub(JobMapper::class);
        $jobMapper->method('findByPrimaryKey')->willReturn($job);
        $jobMapper->method('markJobPendingIfAble')->willReturn(true);
        $controller->runOneGet(
            $this->createStub(LoggerInterface::class),
            $jobMapper,
            $this->createStub(ErrorLoggerInterface::class),
            'default'
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Searching for job default.', $output);
        $this->assertStringContainsString('Job default ran successfully.', $output);
    }

    public function testRunOneWillThrow(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $fakeJob = $this->getMockForAbstractClass(QueueableJob::class);
        $fakeJob->method('run')->willThrowException(new \Exception('Job failed'));

        $job = new Job();
        $job->job_id = 'default';
        $job->tries = 0;
        $job->max_tries = 3;
        $job->status = QueueableJob::JOB_STATUS_PENDING;
        $job->job_context = serialize($fakeJob);

        $jobMapper = $this->createStub(JobMapper::class);
        $jobMapper->method('findByPrimaryKey')->willReturn($job);
        $jobMapper->method('markJobPendingIfAble')->willReturn(true);
        $controller->runOneGet(
            $this->createStub(LoggerInterface::class),
            $jobMapper,
            $this->createStub(ErrorLoggerInterface::class),
            'default'
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Searching for job default.', $output);
        $this->assertStringContainsString('Job default failed.', $output);
    }

    public function testRunOneTriesExceeded(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $fakeJob = $this->getMockForAbstractClass(QueueableJob::class);
        $fakeJob->method('run')->willThrowException(new \Exception('Job failed'));

        $job = new Job();
        $job->job_id = 'default';
        $job->tries = 2;
        $job->max_tries = 3;
        $job->status = QueueableJob::JOB_STATUS_PENDING;
        $job->job_context = serialize($fakeJob);

        $jobMapper = $this->createStub(JobMapper::class);
        $jobMapper->method('findByPrimaryKey')->willReturn($job);
        $jobMapper->method('markJobPendingIfAble')->willReturn(true);
        $controller->runOneGet(
            $this->createStub(LoggerInterface::class),
            $jobMapper,
            $this->createStub(ErrorLoggerInterface::class),
            'default'
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Searching for job default.', $output);
        $this->assertStringContainsString('Job default failed.', $output);
    }

    public function testRunOneInvalidContext(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:listen'])
        );
        $fakeJob = new \stdClass();

        $job = new Job();
        $job->job_id = 'default';
        $job->tries = 2;
        $job->max_tries = 3;
        $job->status = QueueableJob::JOB_STATUS_PENDING;
        $job->job_context = serialize($fakeJob);

        $jobMapper = $this->createStub(JobMapper::class);
        $jobMapper->method('findByPrimaryKey')->willReturn($job);
        $jobMapper->method('markJobPendingIfAble')->willReturn(true);
        $controller->runOneGet(
            $this->createStub(LoggerInterface::class),
            $jobMapper,
            $this->createStub(ErrorLoggerInterface::class),
            'default'
        );
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Searching for job default.', $output);
        $this->assertStringContainsString('Job default contains invalid serialized data.', $output);
    }

    public function testRunCron(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(true);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:run-cron'])
        );
        $now = Date::createFromString('2020-01-01 00:00:00');
        $controller->runCronGet($now, $config);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('nohup php', $output);
    }

    public function testRunCronNoPrivate(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:run-cron'])
        );
        $now = Date::createFromString('2020-01-01 00:00:00');
        $controller->runCronGet($now, $config);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('.txt' . $now->getTimestamp(), $output);
    }

    public function testRunCronItemNoJob(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:run-cron'])
        );

        $controller->runCronItemGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Usage: php famine feast:job:run-cron-item {job}', $output);
    }

    public function testRunCronItemWithOverlap(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:run-cron'])
        );

        $controller->runCronItemGet(MockCronJob::class, 946684800, 1);
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('.txt946684800', $output);
    }

    public function testRunCronItemWrongJob(): void
    {
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnOnConsecutiveCalls(false);
        $controller = new JobController(
            $serviceContainer,
            $config,
            new CliArguments(['famine', 'feast:job:run-cron'])
        );
        $now = Date::createFromString('2020-01-01 00:00:00');

        $controller->runCronItemGet(\stdClass::class, $now->getTimestamp());
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('stdClass is not a CronJob', $output);
    }

}
