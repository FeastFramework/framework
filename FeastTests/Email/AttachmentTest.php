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

namespace Email;

use Feast\Email\Attachment;
use Feast\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

class AttachmentTest extends TestCase
{

    public function testGetAttachmentStringContentText(): void
    {
        $attachment = new Attachment();
        $attachment->setContentId('test');
        $attachment->setContent('testing');
        $attachment->setContentType('text/plain');
        $attachment->setFileName('test.txt');
        $this->assertEquals(
            $this->getExpected(),
            $attachment->getAttachmentString()
        );
    }

    public function testGetAttachmentStringContentFile(): void
    {
        $attachment = new Attachment();
        $attachment->setContentId('test');
        $attachment->setContentFromFile(__DIR__ . '/test.txt');
        $attachment->setContentType('text/plain');
        $attachment->setFileName('test.txt');
        $this->assertEquals(
            $this->getExpected(),
            $attachment->getAttachmentString()
        );
    }

    public function testGetAttachmentWithNoFile(): void
    {
        $attachment = new Attachment();
        $this->expectException(NotFoundException::class);
        $attachment->setContentFromFile('ThisIsNotAFile.txt');
    }

    protected function getExpected(): string
    {
        return "\r\n" . 'Content-Type: text/plain; name="test.txt"' . "\r\n" . 'Content-Transfer-Encoding: base64' . "\r\n" . 'Content-ID: <test>' . "\r\n" . 'Content-Disposition: inline;' . "\r\n" . 'Content-Disposition: attachment; filename="test.txt"' . "\r\n\r\n" . 'dGVzdGluZw==' . "\r\n\r\n\r\n";
    }
}
