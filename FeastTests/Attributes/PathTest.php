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

namespace Attributes;

use Feast\Attributes\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function testGetMethodGetOnly(): void
    {
        $path = new Path('/index', 'index', Path::METHOD_GET);
        $this->assertEquals(['GET'], $path->getMethods());
    }

    public function testGetMethodPostOnly(): void
    {
        $path = new Path('/index', 'index', Path::METHOD_POST);
        $this->assertEquals(['POST'], $path->getMethods());
    }

    public function testGetMethodPutOnly(): void
    {
        $path = new Path('/index', 'index', Path::METHOD_PUT);
        $this->assertEquals(['PUT'], $path->getMethods());
    }

    public function testGetMethodDeleteOnly(): void
    {
        $path = new Path('/index', 'index', Path::METHOD_DELETE);
        $this->assertEquals(['DELETE'], $path->getMethods());
    }

    public function testGetMethodPatchOnly(): void
    {
        $path = new Path('/index', 'index', Path::METHOD_PATCH);
        $this->assertEquals(['PATCH'], $path->getMethods());
    }

    public function testGetMethodGetAndPost(): void
    {
        $path = new Path('/index', 'index', Path::METHOD_GET | Path::METHOD_POST);
        $this->assertEquals(['GET', 'POST'], $path->getMethods());
    }

    public function testGetMethodAll(): void
    {
        $path = new Path('/index', 'index', Path::METHOD_ALL);
        $this->assertEquals(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $path->getMethods());
    }
}