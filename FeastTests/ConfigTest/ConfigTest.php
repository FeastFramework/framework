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

namespace ConfigTest;

use Feast\Config\Config;
use Feast\Exception\ConfigException;
use Feast\Interfaces\ConfigInterface;
use Feast\ServiceContainer\ContainerException;
use Feast\ServiceContainer\ServiceContainer;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{

    public function setUp(): void
    {
        \Feast\Config\TempData::reset();
        di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
    }

    public function testGetSetting(): void
    {
        $config = new Config();
        $this->assertEquals(1, $config->getSetting('test'));
        $this->assertEquals('this_is_secure', $config->getSetting('database.default.password'));
    }

    public function testGetSettingDefault(): void
    {
        $config = new Config();
        $this->assertEquals('nope', $config->getSetting('testNoGo', 'nope'));
    }

    public function testGetSettingNoLocal(): void
    {
        \Feast\Config\TempData::$localExists = false;
        $config = new Config();
        $this->assertEquals('dont_put_passwords_in_the_config_file', $config->getSetting('database.default.password'));
    }

    public function testCacheConfig(): void
    {
        $config = new Config();
        $config->cacheConfig();

        $configFromCache = unserialize(
            \Feast\Config\file_get_contents(
                APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config.cache'
            )
        );
        $this->assertTrue($configFromCache instanceof Config);
    }

    public function testGetEnvironmentNameDefault(): void
    {
        $config = new Config();
        $this->assertEquals('production', $config->getEnvironmentName());
    }

    public function testGetEnvironmentNameOverriden(): void
    {
        $config = new Config(overriddenEnvironment: 'development');
        $this->assertEquals('development', $config->getEnvironmentName());
    }

    public function testGetEnvironmentNameGetEnv(): void
    {
        \Feast\Config\TempData::$env = 'development';
        $config = new Config();
        $this->assertEquals('development', $config->getEnvironmentName());
    }

    public function testGetEnvironmentNameFromDotEnv(): void
    {
        \Feast\Config\file_put_contents(APPLICATION_ROOT . '.appenv', 'development');
        $config = new Config();
        $this->assertEquals('development', $config->getEnvironmentName());
    }

    public function testConstructNotCached(): void
    {
        $config = new Config();
        $this->assertTrue($config instanceof Config);
    }

    public function testConstructNoLocal(): void
    {
        \Feast\Config\TempData::$localExists = false;
        $config = new Config();
        $this->assertTrue($config instanceof Config);
    }

    public function testConstructOverrideEnv(): void
    {
        $config = new Config(overriddenEnvironment: 'development');
        $this->assertTrue($config instanceof Config);
    }

    public function testConstructAfterInContainer(): void
    {
        /** @var ServiceContainer $container */
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = new Config();
        $container->add(ConfigInterface::class, $config);
        $this->expectException(ContainerException::class);
        new Config();
    }

    public function testConstructNoConfigFile(): void
    {
        \Feast\Config\TempData::$allowIni = false;
        $this->expectException(ConfigException::class);
        new Config();
    }

    public function testConstructInvalidEnvironment(): void
    {
        $this->expectException(ConfigException::class);
        new Config(overriddenEnvironment: 'potato');
    }
    
    public function tearDown(): void
    {
        \Feast\Config\TempData::$allowIni = true;
    }
}
