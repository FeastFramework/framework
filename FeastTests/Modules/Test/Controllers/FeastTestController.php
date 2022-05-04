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

namespace Modules\Test\Controllers;

use Feast\Attributes\AccessControl;
use Feast\Attributes\Action;
use Feast\Attributes\Param;
use Feast\Attributes\Path;
use Feast\Date;
use Feast\HttpController;
use Mocks\MockBaseMapper;
use Mocks\MockBaseModel;

class FeastTestController extends HttpController
{
    public function alwaysJson(string $actionName): bool
    {
        return true;
    }

    #[Action(description: 'Clear router cache file (if any) and regenerate')]
    public function routerGenerateGet(): void
    {
    }

    #[Action(usage: 'name [service-name]', description: 'Create a service class from the template file.')]
    #[Param(type: 'string', name: 'name', description: 'Name of service to create')]
    public function serviceGet(
        ?string $name = null
    ): void {
        echo 'Success!';
    }

    #[AccessControl(disabledEnvironments: ['dev'])]
    public function deniedPathForEnvGet(
        ?string $name = null
    ): void {
        echo 'Success!';
    }

    #[AccessControl(onlyEnvironments: ['dev'])]
    public function allowedPathForEnvGet(
        ?string $name = null
    ): void {
        echo 'Success!';
    }

    #[AccessControl(disabledEnvironments: ['production'])]
    public function DeniedPathForProdGet(
        ?string $name = null
    ): void {
        echo 'Success!';
    }

    #[AccessControl(onlyEnvironments: ['production'])]
    public function AllowedPathForProdGet(
        ?string $name = null
    ): void {
        echo 'Success!';
    }

    public function modelGet(
        ?MockBaseModel $model = null,
        ?bool $boolTest = null,
        ?float $floatTest = null,
        ?int $intTest = null,
        ?Date $dateTest = null,
        ?MockBaseModel $nonModel = null,
        ?MockBaseMapper $mapper = null,
        ?array $arrayTest = null,
        string ...$extra
    ): void {
        echo 'Model Success!';
    }

    public function testAction(): void
    {
    }

    public function exceptionGet(): void
    {
        throw new \Exception('Broken controller');
    }

    public function invalidControllerAction(): void
    {
    }
}
