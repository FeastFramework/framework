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

class CsvWriter extends Csv
{
    protected bool $headerWritten = false;

    /**
     * All arguments match the arguments for SplFileObject::__construct();
     * @param string $fileName
     * @param string $mode
     * @param bool $useIncludePath
     * @param null|resource $context
     */
    public function __construct(string $fileName, string $mode = 'w', bool $useIncludePath = false, $context = null)
    {
        $this->file = new SplFileObject($fileName, $mode, $useIncludePath, $context);
        $this->file->setFlags(SplFileObject::READ_CSV);
    }

    /**
     * Set the header/column data for the csv. 
     * 
     * This will be written as the header. In addition,
     * the values are the array keys for any lines written.
     * 
     * @param array $header
     * @return $this
     */
    public function setHeader(array $header): static
    {
        /** @psalm-suppress MixedPropertyTypeCoercion */
        $this->header = array_values($header);
        return $this;
    }

    /**
     * Write the CSV Header. Auto called in writeLine if not called manually.
     */
    public function writeHeader(): void
    {
        if ($this->headerWritten || $this->headerRow === null) {
            return;
        }

        $this->file->rewind();
        while ($this->file->key() < $this->headerRow) {
            $this->moveToNextRow();
        }
        $this->file->fputcsv($this->header);
        if ($this->extraHeaderRowEnd !== null) {
            while ($this->file->key() < $this->extraHeaderRowEnd) {
                $this->moveToNextRow();
            }
        }
        $this->headerWritten = true;
    }

    /**
     * Write a line to the CSV. 
     * 
     * If a header has been set, then those keys are used.
     * Otherwise, written as is.
     *
     * @param array<string> $line
     */
    public function writeLine(array $line): void
    {
        $this->writeHeader();
        $write = [];
        if (empty($this->header)) {
            $write = $line;
        } else {
            foreach ($this->header as $key => $val) {
                $write[$key] = $line[$val] ?? '';
            }
        }
        $this->file->fputcsv($write);
    }

    protected function moveToNextRow(): void
    {
        $this->file->fputcsv([]);
        $this->file->next();
    }

}
