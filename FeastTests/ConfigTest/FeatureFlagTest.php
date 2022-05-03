<?php

/**
 * Copyright 2022 Jeremy Presutti <Jeremy@Presutti.us>
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

namespace ConfigTest;

use Feast\Config\Config;
use Feast\Enums\ServiceContainer;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\FeatureFlagInterface;
use PHPUnit\Framework\TestCase;

class FeatureFlagTest extends TestCase
{

    public function setUp(): void
    {
        \Feast\Config\TempData::reset();
        di(null, ServiceContainer::CLEAR_CONTAINER);
    }

    public function testFeatureFlagInit(): void
    {
        $config = new Config();
        $featureFlag = $config->getFeatureFlags();
        $this->assertInstanceOf(FeatureFlagInterface::class, $featureFlag['test']);
    }

    public function testFeatureFlagInitBroken(): void
    {
        $config = new Config(overriddenEnvironment: 'development');
        $this->expectException(ServerFailureException::class);
        $config->getFeatureFlags();
    }

    public function testGetFeatureFlagByName(): void
    {
        $config = new Config();
        $featureFlag = $config->getFeatureFlag('test');
        $this->assertInstanceOf(FeatureFlagInterface::class, $featureFlag);
    }

    public function testFeatureFlagEnabledTrue(): void
    {
        $config = new Config();
        $featureFlag = $config->getFeatureFlag('test');
        $this->assertTrue($featureFlag->isEnabled());
    }

    public function testFeatureFlagEnabledFalse(): void
    {
        $config = new Config();
        $featureFlag = $config->getFeatureFlag('otherTest');
        $this->assertFalse($featureFlag->isEnabled());
    }

    public function testFeatureFlagEnabledDefaultTrue(): void
    {
        $config = new Config();
        $featureFlag = $config->getFeatureFlag('noflag', true);
        $this->assertTrue($featureFlag->isEnabled());
    }

    public function testFeatureFlagEnabledDefaultFalse(): void
    {
        $config = new Config();
        $featureFlag = $config->getFeatureFlag('noflag');
        $this->assertFalse($featureFlag->isEnabled());
    }

    public function testFeatureFlagOverriddenEnabledTrueToFalse(): void
    {
        $config = new Config(overriddenEnvironment: 'features');
        $featureFlag = $config->getFeatureFlag('trueTest');
        $this->assertFalse($featureFlag->isEnabled());
    }

    public function testFeatureFlagOverridenEnabledFalseToTrue(): void
    {
        $config = new Config(overriddenEnvironment: 'features');
        $featureFlag = $config->getFeatureFlag('falseTest');
        $this->assertTrue($featureFlag->isEnabled());
    }

    public function testFeatureFlagNonOverriddenParentItem(): void
    {
        $config = new Config(overriddenEnvironment: 'features');
        $featureFlag = $config->getFeatureFlag('test');
        $this->assertTrue($featureFlag->isEnabled());
    }

}
