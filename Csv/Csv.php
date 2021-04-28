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

namespace Feast\Csv;

use SplFileObject;

abstract class Csv
{
    protected SplFileObject $file;

    protected ?int $headerRow = null;
    protected ?int $extraHeaderRowEnd = null;

    /** @var array<string>  */
    protected array $header = [];

    /**
     * Set the Header row and any other optional rows to be marked as header.
     *
     * Any row less than or equal to the header row will not be returned.
     *
     * @param int $headerRow
     * @param int|null $extraHeaderRowEnd
     * @return $this
     */
    public function setHeaderRow(int $headerRow, ?int $extraHeaderRowEnd = null): static
    {
        $this->headerRow = $headerRow;

        $this->extraHeaderRowEnd = $extraHeaderRowEnd;
        return $this;
    }

    /**
     * Set CSV options for file.
     * 
     * @param string $separator
     * @param string $enclosure
     * @param string $escape
     * @return $this
     */
    public function setCsvOptions(string $separator = ',', string $enclosure = '"', string $escape = '\\'): static
    {
        $this->file->setCsvControl($separator, $enclosure, $escape);
        return $this;
    }

    /**
     * Get the underlying file object.
     * 
     * @return SplFileObject
     */
    public function getFileHandler(): SplFileObject {
        return $this->file;
    }

    abstract protected function moveToNextRow(): void;
    
}
