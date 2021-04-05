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

namespace Jobs;

use Feast\Interfaces\JobInterface;
use Mapper\JobMapper;
use Mocks\MockJob;
use Model\Job;
use PHPUnit\Framework\TestCase;

class QueueableJobTest extends TestCase
{

    public function testStore()
    {
        $mock = new MockJob();
        $result = $mock->store($this->createStub(JobMapper::class));
        $this->assertTrue($result instanceof Job);
        $this->assertEquals(36, strlen($result->job_id));
    }
    
    public function testSetName()
    {
        $mock = new MockJob();
        $mock->setJobName('NewName');
        $this->assertEquals('NewName',$mock->getJobName());
    }

    public function testSetQueue()
    {
        $mock = new MockJob();
        $mock->setQueueName('NewQueue');
        $this->assertEquals('NewQueue',$mock->getQueueName());
    }

    public function testSetMaxTries()
    {
        $mock = new MockJob();
        $mock->setMaxTries(4);
        $this->assertEquals(4,$mock->getMaxTries());
    }

    public function testRun()
    {
        $mock = new MockJob();
        $result = $mock->run();
        $this->assertTrue($result);
    }

    public function testRunOnInterface()
    {
        $mock = $this->getMockForAbstractClass(JobInterface::class);
        $result = $mock->run();
        $this->assertFalse($result);
    }
    
}
