<?php

/**
 * Copyright 2023 Jeremy Presutti <Jeremy@Presutti.us>
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

use PHPUnit\Framework\TestCase;

class DeferredTest extends TestCase
{
    public function testDeferredMethod(): void
    {
        $this->deferredTester();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('This is firstThis is second', $output);
    }

    public function testCancelledDeferredMethod(): void
    {
        $this->deferredTester(true);
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('This is first', $output);
    }

    public function testDeferredCallable(): void
    {
        $this->deferredCallableTester();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('This is firstThis is second', $output);
    }

    public function testDeferredCallableEnsureOrder(): void
    {
        $this->deferredCallableTesterEnsureProcessingOrder();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('This is firstThis is second', $output);
    }

    public function testCancelledDeferredCallable(): void
    {
        $this->deferredCallableTester(true);
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals('This is first', $output);
    }

    protected function deferredTester(bool $cancel = false): void
    {
        $deferred = new \Mocks\DeferredMock('This is second');
        echo 'This is first';
        if ( $cancel ) {
            $deferred->cancelDeferral();
        }
    }

    protected function deferredCallableTester(bool $cancel = false): void
    {
        $deferred = new \Feast\DeferredCall(function() { echo 'This is second'; });
        echo 'This is first';
        if ( $cancel ) {
            $deferred->cancelDeferral();
        }
    }

    protected function deferredCallableTesterEnsureProcessingOrder(bool $cancel = false): void
    {
        $output = 'This is second';
        $deferred = new \Feast\DeferredCall(function() use ($output) { echo $output; });
        $output = 'This is not second';
        echo 'This is first';
        if ( $cancel ) {
            $deferred->cancelDeferral();
        }
    }
    
}
