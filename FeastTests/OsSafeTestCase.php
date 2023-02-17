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

abstract class OsSafeTestCase extends TestCase
{
    protected function assertEqualsIgnoreLineEndingDiff(string $expected, string $actual): void
    {
        $this->assertEquals(str_replace("\r\n", "\n", $expected), str_replace("\r\n", "\n", $actual));
    }

    protected function assertStringContainsStringIgnoreLineEndingDiff(string $needle, string $haystack): void
    {
        $this->assertStringContainsString(str_replace("\r\n", "\n", $needle), str_replace("\r\n", "\n", $haystack));
    }

    protected function assertStringStartsWithIgnoreLineEndingDiff(string $prefix, string $haystack): void
    {
        $this->assertStringStartsWith(str_replace("\r\n", "\n", $prefix), str_replace("\r\n", "\n", $haystack));
    }

    protected function linuxifyTestOutput(string $output): string
    {
        return str_replace('Model\\Generated\\ ', 'Model/Generated/ ', $output);
    }
}
