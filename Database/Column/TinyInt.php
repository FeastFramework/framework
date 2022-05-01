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

namespace Feast\Database\Column;

use Feast\Exception\DatabaseException;

class TinyInt extends Integer
{
    public const TYPE = 'tinyint';

    /**
     * Create TinyInt column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $unsigned
     * @param bool $nullable
     * @param int|null $default
     * @param string|null $comment
     * @throws DatabaseException
     */
    public function __construct(string $name, int $length = 3, bool $unsigned = false, bool $nullable = false, ?int $default = null, ?string $comment = null)
    {
        parent::__construct($name, $length, $unsigned, $nullable, $default, $comment);
    }
}
