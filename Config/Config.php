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

namespace Feast\Config;

use Feast\Exception\ConfigException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\ConfigInterface;
use Feast\ServiceContainer\ContainerException;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;
use stdClass;

/**
 *
 * Class to load and manage the configuration files.
 */
class Config implements ServiceContainerItemInterface, ConfigInterface
{
    use DependencyInjected;

    private stdClass $config;
    private string $env;

    /**
     * Initial creation of \Feast\Config
     *
     * @param bool $pullFromContainer - True to check if already in service container
     * @param string|null $overriddenEnvironment - if set, the environment will be the one passed in
     * @throws ServerFailureException|ContainerException|NotFoundException
     */
    public function __construct(bool $pullFromContainer = true, string $overriddenEnvironment = null)
    {
        if ($pullFromContainer) {
            $this->checkInjected();
        }

        $this->env = $overriddenEnvironment ?? $this->getEnvironmentForConfig();

        $this->config = $this->buildConfigFromFile();
        $this->addLocalConfig();
    }

    /**
     * Cache the config and store on disk
     */
    public function cacheConfig(): void
    {
        $configFileName = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config.cache';
        file_put_contents($configFileName, serialize($this));
    }

    /**
     * Get current environment
     *
     * @return string
     */
    public function getEnvironmentName(): string
    {
        return $this->env;
    }

    /**
     * Get config setting. Returns default if setting not found.
     * 
     * The Config key can be a parent value or nested via "." separation
     * If a "." is in the key, the settings will be fetched recursively.
     * The default will be returned if any key in the path is not found. 
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $currentConfigItem = $this->config;

        foreach ($keys as $configKey) {
            if (isset($currentConfigItem->$configKey)) {
                /** @var stdClass $currentConfigItem */
                $currentConfigItem = $currentConfigItem->$configKey;
            } else {
                return $default;
            }
        }

        return $currentConfigItem;
    }

    /**
     * Builds out the config file in order, one section at a time
     * 
     * @param array $config
     * @return stdClass
     */
    protected function buildConfigData(array $config): stdClass
    {
        $configData = new stdClass();

        /**
         * @var string $sectionName
         * @var array $section
         */
        foreach ($config as $sectionName => $section) {
            $this->buildEnvironment($configData, $sectionName, $section);
        }

        return $configData;
    }

    /**
     * Build config environment, running through inheritance rules.
     * 
     * @param stdClass $configData
     * @param string $environmentName
     * @param array $section
     */
    protected function buildEnvironment(stdClass $configData, string $environmentName, array $section): void
    {
        // check if section builds on another section (environment)
        $this->buildInheritedEnvironments($configData, $environmentName);
        /** @var stdClass $sectionData */
        $sectionData = $configData->$environmentName;

        $sectionData = $this->configMergeRecursive($sectionData, $section);

        $configData->$environmentName = (object)$sectionData;
    }

    /**
     * Recursively merge config settings from one environment to another.
     * 
     * @param stdClass $currentEnvironment
     * @param array<array-key,mixed> $parentEnvironment
     * @return array
     */
    protected function configMergeRecursive(stdClass $currentEnvironment, array $parentEnvironment): array
    {
        /** @var array<array-key,int|string|bool|float|array<array-key,mixed>> $baselineConfig */
        $baselineConfig = $this->objectToArray($currentEnvironment);
       
        /**
         * @var string $keyBase
         * @var string|int|bool|float|array $val
         */
        foreach ($parentEnvironment as $keyBase => $val) {
            /** @var array<int,string> $keyList */
            $key = explode('.', $keyBase);
            $lastKey = array_pop($key);
            
            // Config item is assigned via reference for nesting buildout.
            $configItem = &$baselineConfig;
            foreach ($key as $currentKey) {
                if (!isset($configItem[$currentKey])) {
                    $configItem[$currentKey] = [];
                }
                /** @var array $configItem */
                $configItem = &$configItem[$currentKey];
            }
            
            // If an array, recursively merge the next level
            if (is_array($val)) {
                if (!isset($configItem[$lastKey]) || !is_array($configItem[$lastKey])) {
                    $configItem[$lastKey] = [];
                }
                $configItem[$lastKey] = $this->configMergeRecursive((object)$configItem[$lastKey], $val);
            } else {
                $configItem[$lastKey] = $val;
            }
        }
        return $baselineConfig;
    }

    /**
     * Scans the config file and builds configuration.
     *
     * @return stdClass
     * @throws ServerFailureException if file unreadable, unable to be parsed, or does not exist
     */
    private function buildConfigFromFile(): stdClass
    {
        if (!file_exists(APPLICATION_ROOT . 'configs' . DIRECTORY_SEPARATOR . 'config.php') || !is_readable(
                APPLICATION_ROOT . 'configs' . DIRECTORY_SEPARATOR . 'config.php'
            )) {
            throw new ConfigException('Config file not found or is not readable');
        }
        /** @var array<array-key,mixed> $config */
        $config = include(APPLICATION_ROOT . 'configs' . DIRECTORY_SEPARATOR . 'config.php');

        $configData = $this->buildConfigData($config);
        /** @var ?stdClass $currentConfig */
        $currentConfig = $configData->{$this->env} ?? null;
        if ($currentConfig instanceof stdClass) {
            return $this->cloneObjectOrArrayAsObject($currentConfig);
        }

        throw new ConfigException('Error creating config');
    }

    /**
     * Determine the correct env for the current running application.
     * 
     * Environment is determined in the following order:
     *     1. Web server/process ENV setting
     *     2. .appenv file (contains just env name)
     *     3. defaults to "production"
     *
     * @return string
     */
    private function getEnvironmentForConfig(): string
    {
        // Sets environment based on web server/process environment variable.
        $environment = getenv('APPLICATION_ENV');
        if ($environment !== false) {
            return $environment;
        }

        if (file_exists(APPLICATION_ROOT . '.appenv')) {
            $environment = file_get_contents(APPLICATION_ROOT . '.appenv');

            if (!empty($environment)) {
                return trim($environment);
            }
        }
        
        return 'production';
    }

    /**
     * Build environment config by name.
     * 
     * Names are colon separation based inheritance. Example:
     *     1. "production" - all settings are standalone
     *     2. "production : development" - production is cloned, then development settings are applied.
     *
     * @param stdClass $config
     * @param string $environmentName
     */
    private function buildInheritedEnvironments(stdClass $config, string &$environmentName): void
    {
        $environmentInheritance = explode(':', str_replace(' ', '', $environmentName));

        // The last name for the section is the actual environment.
        $environmentName = array_pop($environmentInheritance);

        $config->$environmentName = new stdClass();
        foreach ($environmentInheritance as $parentEnvironment) {
            /**
             * @var string $key
             * @var string|int|bool|stdClass $val
             */
            foreach ($config->{$parentEnvironment} as $key => $val) {
                $config->$environmentName->$key = $val instanceof stdClass ? $this->cloneObjectOrArrayAsObject($val) : $val;
            }
        }
    }

    /**
     * Override all config options from configs/config.local.php if it exists
     */
    private function addLocalConfig(): void
    {
        if (!file_exists(APPLICATION_ROOT . 'configs' . DIRECTORY_SEPARATOR . 'config.local.php')) {
            return;
        }
        /** @var array<array-key,mixed> $localConfig */
        $localConfig = include(APPLICATION_ROOT . 'configs' . DIRECTORY_SEPARATOR . 'config.local.php');

        $this->config = $this->cloneObjectOrArrayAsObject(
            $this->configMergeRecursive($this->config, $localConfig)
        );
    }

    private function cloneObjectOrArrayAsObject(stdClass|array $settings): stdClass
    {
        /** @var stdClass $config */
        $config = json_decode(json_encode($settings));

        return $config;
    }

    private function objectToArray(stdClass $object): array
    {
        /** @var array $config */
        $config = json_decode(json_encode($object), true);

        return $config;
    }

}
