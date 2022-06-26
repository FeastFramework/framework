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

namespace Feast\Traits;

use Feast\Date;

trait DebugQuery
{
    /**
     * @param string $query
     * @param array<string|int|float|bool|Date|null> $bindings
     * @return string
     */
    public function debugQuery(string $query, array $bindings): string
    {
        /** @var string|int|float|bool|Date|null $binding */
        foreach ($bindings as $binding) {
            $location = strpos($query, '?');
            if ($location === false) {
                return $query;
            }
            $query = substr_replace(
                $query,
                '\'' . str_replace('?', '{question_mark}', (string)$binding) . '\'',
                $location,
                1
            );
        }

        return str_replace('{question_mark}', '?', $query);
    }

}
