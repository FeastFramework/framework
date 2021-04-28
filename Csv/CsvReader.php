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

class CsvReader extends Csv
{
    protected bool $headerBuilt = false;

    /**
     * @param string $fileName
     * @param string $mode
     * @param bool $useIncludePath
     * @param null|resource $context
     */
    public function __construct(string $fileName, string $mode = 'r', bool $useIncludePath = false, $context = null)
    {
        $this->file = new SplFileObject($fileName, $mode, $useIncludePath, $context);
        $this->file->setFlags(SplFileObject::READ_CSV);
    }

    /**
     * Get the header from the file if setHeaderRow has been called. Otherwise, false.
     *
     * @return array|false
     */
    public function getHeader(): array|false
    {
        $this->buildHeader();
        return $this->header;
    }

    /**
     * Get yielded values for reader.
     *
     * @return \Generator
     */
    public function getIterator(): \Generator
    {
        $this->rewind();
        while ($row = $this->getNextLine()) {
            yield $row;
        }
    }

    /**
     * Get all values as array.
     *
     * WARNING: On large files, this will utilize a large amount of memory.
     *
     * @return array
     */
    public function getAll(): array
    {
        $return = [];
        while ($row = $this->getNextLine()) {
            $return[] = $row;
        }

        return $return;
    }

    /**
     * Get the next line from the CSV.
     *
     * @return array|false
     */
    public function getNextLine(): array|false
    {
        if ($this->headerBuilt === false) {
            $this->buildHeader();
        }
        $return = [];
        $row = $this->file->fgetcsv();
        if ($row === null || $row === false) {
            return false;
        }

        foreach ($row as $key => $val) {
            $itemName = $this->header[$key] ?? $key;
            $return[$itemName] = $val;
        }

        return $return;
    }

    /**
     * Rewind to the beginning of the CSV's data. Skips past header rows.
     */
    public function rewind(): void
    {
        $this->file->rewind();
        if ($this->headerRow === null) {
            return;
        }
        while ($this->file->key() <= $this->headerRow) {
            $this->moveToNextRow();
        }
        if ($this->extraHeaderRowEnd === null) {
            return;
        }
        while ($this->file->key() <= $this->extraHeaderRowEnd) {
            $this->moveToNextRow();
        }
    }

    protected function moveToNextRow(): void
    {
        $this->file->current();
        $this->file->next();
    }

    protected function buildHeader(): void
    {
        if ($this->headerBuilt) {
            return;
        }
        $this->headerBuilt = true;
        if ($this->headerRow === null) {
            return;
        }
        $this->file->rewind();
        while ($this->file->key() < $this->headerRow) {
            $this->moveToNextRow();
        }

        /** @var array<string>|false $row */
        $row = $this->file->fgetcsv();
        if (is_array($row)) {
            $this->header = $row;
        }

        if ($this->extraHeaderRowEnd !== null) {
            while ($this->file->key() <= $this->extraHeaderRowEnd) {
                $this->moveToNextRow();
            }
        }
    }

}
