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

namespace Csv;

use Feast\Csv\CsvWriter;
use PHPUnit\Framework\TestCase;

class CsvWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $file = new CsvWriter('php://temp', 'w+');
        $file->setHeaderRow(1, 3);
        $file->setHeader(['name', 'language', 'years']);
        $file->writeLine(
            [
                'name' => 'Jeremy',
                'language' => 'PHP',
                'years' => '16'
            ]
        );

        $file->writeLine(
            [
                'name' => 'German',
                'years' => '6',
                'language' => 'JavaScript',
            ]
        );

        $file->writeLine(
            [
                'years' => 0,
                'name' => 'Erin',
                'language' => 'HTML',
            ]
        );

        $file = $file->getFileHandler();
        $file->rewind();
        $file->fpassthru();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals(
            str_replace("\r\n","\n",'
name,language,years


Jeremy,PHP,16
German,JavaScript,6
Erin,HTML,0
'),
            str_replace("\r\n","\n",$output)
        );
    }

    public function testWriteNoHeader(): void
    {
        $file = new CsvWriter('php://temp', 'w+');

        $file->writeLine(
            [
                'Jeremy',
                'PHP',
                '16'
            ]
        );

        $file->writeLine(
            [
                'German',
                'JavaScript',
                '6'
            ]
        );

        $file->writeLine(
            [
                'Erin',
                'HTML',
                0
            ]
        );

        $file = $file->getFileHandler();
        $file->rewind();
        $file->fpassthru();
        $output = $this->getActualOutputForAssertion();
        $this->assertEquals(
            str_replace("\r\n","\n",'Jeremy,PHP,16
German,JavaScript,6
Erin,HTML,0
'),
            $output
        );
    }
}
