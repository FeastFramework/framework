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

namespace Modules\CLI\Controllers;

use Feast\Attributes\Action;
use Feast\Attributes\Param;

class FeastTestController
{
    #[Action(description: 'Clear router cache file (if any) and regenerate')]
    public function routerGenerateGet(): void
    {
    }

    #[Action(description: 'Clear router cache file (if any) and regenerate')]
    public function routerGenerateReallyLongNameTestThingsGet(): void
    {
    }

    #[Action(usage: 'name [service-name]', description: 'Create a service class from the template file.')]
    #[Param(type: 'string', name: 'name', description: 'Name of service to create')]
    public function serviceGet(
        ?string $name = null
    ): void {
    }

    public function testAction(): void
    {
    }

    public function invalidControllerAction(): void
    {
    }
}
