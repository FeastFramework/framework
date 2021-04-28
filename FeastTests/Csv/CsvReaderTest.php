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

use Feast\Csv\CsvReader;
use PHPUnit\Framework\TestCase;

class CsvReaderTest extends TestCase
{
    public function testRead(): void
    {
        $reader = new CsvReader('php://temp', 'w+');
        $reader->setHeaderRow(1, 3);

        $file = $reader->getFileHandler();
        $file->rewind();
        $file->fwrite(
            '
name,language,years


Jeremy,PHP,16
German,JavaScript,6
Erin,HTML,0
'
        );
        $file->rewind();

        $header = $reader->getHeader();
        $this->assertEquals(
            [
                'name',
                'language',
                'years'
            ],
            $header
        );
        $data = $reader->getAll();
        $this->assertEquals(
            [
                'name' => 'Jeremy',
                'language' => 'PHP',
                'years' => '16'
            ],
            $data[0]
        );

        $this->assertEquals(
            [
                'name' => 'German',
                'language' => 'JavaScript',
                'years' => '6'
            ],
            $data[1]
        );

        $this->assertEquals(
            [
                'name' => 'Erin',
                'language' => 'HTML',
                'years' => '0'
            ],
            $data[2]
        );

        $count = 0;
        foreach ($reader->getIterator() as $row ) {
            $count++;
        }
        $this->assertEquals(3,$count);
        $reader->rewind();
        $reader->getHeader();
    }

    public function testReadNoExtraHeader(): void
    {
        $reader = new CsvReader('php://temp', 'w+');
        $reader->setHeaderRow(1);

        $file = $reader->getFileHandler();
        $file->rewind();
        $file->fwrite(
            '
name,language,years
Jeremy,PHP,16
German,JavaScript,6
Erin,HTML,0
'
        );
        $file->rewind();

        $header = $reader->getHeader();
        $this->assertEquals(
            [
                'name',
                'language',
                'years'
            ],
            $header
        );
        $data = $reader->getAll();
        $this->assertEquals(
            [
                'name' => 'Jeremy',
                'language' => 'PHP',
                'years' => '16'
            ],
            $data[0]
        );

        $this->assertEquals(
            [
                'name' => 'German',
                'language' => 'JavaScript',
                'years' => '6'
            ],
            $data[1]
        );

        $this->assertEquals(
            [
                'name' => 'Erin',
                'language' => 'HTML',
                'years' => '0'
            ],
            $data[2]
        );

        $count = 0;
        foreach ($reader->getIterator() as $row ) {
            $count++;
        }
        $this->assertEquals(3,$count);
        $reader->rewind();
    }

    public function testReadNoHeader(): void
    {
        $reader = new CsvReader('php://temp', 'w+');

        $file = $reader->getFileHandler();
        $file->rewind();
        $file->fwrite(
            'Jeremy,PHP,16
German,JavaScript,6
Erin,HTML,0
'
        );
        $file->rewind();
        
        $data = $reader->getAll();
        $this->assertEquals(
            [
                'Jeremy',
                'PHP',
                '16'
            ],
            $data[0]
        );

        $this->assertEquals(
            [
                'German',
                'JavaScript',
                '6'
            ],
            $data[1]
        );

        $this->assertEquals(
            [
                'Erin',
                'HTML',
                '0'
            ],
            $data[2]
        );

        $count = 0;
        foreach ($reader->getIterator() as $row ) {
            $count++;
        }
        $this->assertEquals(3,$count);
    }

    public function testReadOptionsChange(): void
    {
        $reader = new CsvReader('php://temp', 'w+');
        $reader->setHeaderRow(1, 3);
        $reader->setCsvOptions(';');
        $file = $reader->getFileHandler();
        $file->rewind();
        $file->fwrite(
            '
name;language;years


Jeremy;PHP;16
German;JavaScript;6
Erin;HTML;0
'
        );
        $file->rewind();

        $header = $reader->getHeader();
        $this->assertEquals(
            [
                'name',
                'language',
                'years'
            ],
            $header
        );
        $data = $reader->getAll();
        $this->assertEquals(
            [
                'name' => 'Jeremy',
                'language' => 'PHP',
                'years' => '16'
            ],
            $data[0]
        );

        $this->assertEquals(
            [
                'name' => 'German',
                'language' => 'JavaScript',
                'years' => '6'
            ],
            $data[1]
        );

        $this->assertEquals(
            [
                'name' => 'Erin',
                'language' => 'HTML',
                'years' => '0'
            ],
            $data[2]
        );

        $count = 0;
        foreach ($reader->getIterator() as $row ) {
            $count++;
        }
        $this->assertEquals(3,$count);

        $reader->rewind();
        $reader->setCsvOptions();
        $data = $reader->getAll();
        $this->assertEquals(
            [
                'name' => 'Jeremy;PHP;16'
            ],
            $data[0]
        );
    }
}
