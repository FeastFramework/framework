<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
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
use Feast\Email\Email;
use Feast\Exception\InvalidArgumentException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\ConfigInterface;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{

    public function testConstruct(): void
    {
        $configStub = $this->createStub(ConfigInterface::class);
        $configStub->method('getSetting')->willReturn('/usr/bin/sendmail');
        $email = new Email($configStub);
        $this->assertTrue($email instanceof Email);
    }

    public function testEmail(): void
    {
        $configStub = $this->createStub(ConfigInterface::class);
        $configStub->method('getSetting')->willReturn('/usr/bin/sendmail');

        $attachment1 = $this->createStub(Attachment::class);
        $attachment1->method('getAttachmentString')->willReturn('TestAttachment');
        $attachment2 = $this->createStub(Attachment::class);
        $attachment2->method('getAttachmentString')->willReturn('FeastAttachment');
        $email = new Email($configStub);
        $email->addRecipient('feast@example.com', 'Feast')
            ->addBCC('feast2@example.com')
            ->addCC('feast3@example.com', 'FeastyBoys')
            ->setFrom('framework@example.com', 'Feast Framework')
            ->setSubject('This is a test')
            ->setHtmlBody('<p>Test</p>')
            ->setTextBody('Test')
            ->addAttachment($attachment1)
            ->addInlineAttachment($attachment2)
            ->setReplyTo('no-reply@example.com', 'No Thank You');

        $email->sendEmail();
        $output = $this->getActualOutputForAssertion();
        /** @noinspection PhpUndefinedFunctionInspection */
        $this->assertEquals(\Feast\Email\getBody(), $output);
    }

    public function testEmailNoCcBccReplyTo(): void
    {
        $configStub = $this->createStub(ConfigInterface::class);
        $configStub->method('getSetting')->willReturn('/usr/bin/sendmail');

        $attachment1 = $this->createStub(Attachment::class);
        $attachment1->method('getAttachmentString')->willReturn('TestAttachment');
        $attachment2 = $this->createStub(Attachment::class);
        $attachment2->method('getAttachmentString')->willReturn('FeastAttachment');
        $email = new Email($configStub);
        $email->addRecipient('feast@example.com', 'Feast')
            ->setFrom('framework@example.com', 'Feast Framework')
            ->setSubject('This is a test')
            ->setHtmlBody('<p>Test</p>')
            ->setTextBody('Test')
            ->addAttachment($attachment1)
            ->addInlineAttachment($attachment2);

        $email->sendEmail();
        $output = $this->getActualOutputForAssertion();
        /** @noinspection PhpUndefinedFunctionInspection */
        $this->assertEquals(\Feast\Email\getBodyNoCcBccReplyTo(), $output);
    }

    public function testEmailNoFrom(): void
    {
        $configStub = $this->createStub(ConfigInterface::class);
        $configStub->method('getSetting')->willReturn('/usr/bin/sendmail');

        $email = new Email($configStub);
        $email->addRecipient('feast@example.com', 'Feast')
            ->setReplyTo('no-reply@example.com', 'No Thank You');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No from address specified');
        $email->sendEmail();
    }

    public function testEmailFailedOpen(): void
    {
        $configStub = $this->createStub(ConfigInterface::class);
        $configStub->method('getSetting')->willReturn('/usr/bin/sendmailfailure');

        $email = new Email($configStub);
        $email->addRecipient('feast@example.com', 'Feast')
            ->setFrom('framework@example.com', 'Feast Framework')
            ->setReplyTo('no-reply@example.com', 'No Thank You');
        $this->expectException(ServerFailureException::class);
        $this->expectExceptionMessage('Could not open email process');
        $email->sendEmail();
    }
}
