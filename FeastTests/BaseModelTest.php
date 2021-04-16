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

use Feast\BaseModel;
use Feast\Exception\InvalidOptionException;
use Feast\Exception\NotFoundException;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\ServiceContainer\ServiceContainer;
use PHPUnit\Framework\TestCase;

class BaseModelTest extends TestCase
{

    public function testModelChangeTracking(): void
    {
        $model = new \Mocks\MockBaseModel();
        $model->id = 1;
        $model->theName = 'Feast';
        $model->makeOriginalModel();

        $model->theName = 'Feasty';
        $this->assertEquals($model->id, $model->getOriginalModel()->id);
        $this->assertNotEquals($model->theName, $model->getOriginalModel()->theName);
        $this->assertEquals('Feast', $model->getOriginalModel()->theName);
    }

    public function testGetChanges(): void
    {
        $model = new \Mocks\MockBaseModel();
        $model->id = 1;
        $model->theName = 'Feast';
        $model->makeOriginalModel();

        $model->theName = 'Feasty';
        $model->passEncrypted = 'gibberish';
        $changes = $model->getChanges($model->getOriginalModel());
        $this->assertEquals(['TheName Changed From "Feast" to "Feasty"', 'PassEncrypted Changed'], $changes);
    }

    public function testSet(): void
    {
        $this->expectException(InvalidOptionException::class);
        $model = new \Mocks\MockBaseModel();
        $model->nope = 'test';
    }

    public function testGet(): void
    {
        $this->expectException(InvalidOptionException::class);
        $model = new \Mocks\MockBaseModel();
        $test = $model->nope;
    }

    public function testSave(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $mockDBF = $this->createStub(DatabaseFactoryInterface::class);
        $container->add(DatabaseFactoryInterface::class, $mockDBF);
        $model = new \Mocks\MockBaseModel();
        $model->id = 1;
        $model->theName = 'Feast';
        $model->makeOriginalModel();

        $model->theName = 'Feasty';
        $model->save();
        $this->assertTrue($model instanceof BaseModel);
    }

    public function testSaveNoMapper(): void
    {
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $mockDBF = $this->createStub(DatabaseFactoryInterface::class);
        $container->add(DatabaseFactoryInterface::class, $mockDBF);
        $model = new \Mocks\MockBaseModelNoMapper();
        $model->id = 1;
        $model->theName = 'Feast';
        $model->makeOriginalModel();

        $model->theName = 'Feasty';
        $this->expectException(NotFoundException::class);
        $model->save();
    }
}
