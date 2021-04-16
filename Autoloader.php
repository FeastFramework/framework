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

namespace Feast;

/**
 * Autoloader class PSR4 compliant
 */
class Autoloader
{
    /** @var array<array> */
    protected array $pathMappings = [];
    /** @var string[] */
    protected array $extensions = ['.php'];

    /**
     * Add path mapping for a namespace. - Replaces existing.
     *
     * @param string $namespace
     * @param array $path
     */
    public function addPathMapping(string $namespace, array $path): void
    {
        $this->pathMappings[$namespace] ??= [];
        $this->pathMappings[$namespace]  = array_merge($this->pathMappings[$namespace], $path);
    }

    /**
     * Register the Autoloader with optional file extensions.
     *
     * @param string[] $extensions
     */
    public function register(array $extensions = ['.php']): void
    {
        $this->extensions = $extensions;
        spl_autoload_register([$this, 'loadClass'], true, true);
        $this->addPathMapping(
            'Psr',
            [
                'Feast\Psr'
            ]
        );
    }

    /**
     * Load class file by class name.
     *
     * @param string $class
     */
    public function loadClass(string $class): void
    {
        /** @var array{0:array<string>,1:string} $path */
        $path = $this->getMappings($class);
        $this->loadFile($path[0], $path[1]);
    }

    /**
     * Load file by Path list/Class name.
     *
     * @param array<string> $paths
     * @param string $class
     * @return bool
     */
    protected function loadFile(array $paths, string $class): bool
    {
        foreach ($paths as $path) {
            foreach ($this->extensions as $extension) {
                $fullPath = str_replace('\\', DIRECTORY_SEPARATOR, $path . '\\' . $class) . $extension;
                if (file_exists(APPLICATION_ROOT . $fullPath)) {
                    /** @noinspection PhpIncludeInspection */
                    /** @psalm-suppress UnresolvableInclude */
                    require_once(APPLICATION_ROOT . $fullPath);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get path mappings by class name.
     *
     * @param string $class
     * @return array
     */
    protected function getMappings(string $class): array
    {
        $namespaceSplit = explode('\\', $class);
        $sectionCount = count($namespaceSplit) - 1;
        for ($i = $sectionCount; $i > 0; $i--) {
            $newSplit = array_slice($namespaceSplit, 0, $i);
            $postSplit = array_slice($namespaceSplit, $i);
            $path = implode('\\', $newSplit);
            if (isset($this->pathMappings[$path])) {
                return [$this->pathMappings[$path], implode('\\', $postSplit)];
            }
        }
        $class = array_pop($namespaceSplit);
        $path = implode('\\', $namespaceSplit);

        return [[$path], $class];
    }
}
