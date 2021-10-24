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

namespace Feast\Email;

use Feast\Exception\InvalidArgumentException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\ConfigInterface;

class Email
{
    /** @var null|Recipient $from */
    private ?Recipient $from = null;
    /** @var array<Recipient> $to */
    private array $to = [];
    private string $separator;
    private ?string $sendmailPath;
    private array $smtpDetails = [];
    private string $subject = '<No Subject>';
    /** @var array<Recipient> $cc */
    private array $cc = [];
    /** @var array<Recipient> $bcc */
    private array $bcc = [];
    private ?string $htmlBody = null;
    private ?string $plainTextBody = null;
    private array $attachments = [];
    protected array $inlineAttachments = [];
    private ?Recipient $replyTo = null;

    public function __construct(ConfigInterface $config)
    {
        $this->sendmailPath = (string)$config->getSetting('email.sendmailpath');
        $this->separator = substr(md5((string)time()), 0, 6);
    }

    /**
     * Add recipient to email (with optional name).
     *
     * @param string $email
     * @param string|null $name
     * @return static
     */
    public function addRecipient(string $email, ?string $name = null): static
    {
        $recipient = new Recipient($email, $name);
        $this->to[] = $recipient;

        return $this;
    }

    /**
     * Add cc'ed recipient to email (with optional name).
     *
     * @param string $email
     * @param string|null $name
     * @return static
     */
    public function addCC(string $email, ?string $name = null): static
    {
        $recipient = new Recipient($email, $name);
        $this->cc[] = $recipient;

        return $this;
    }

    /**
     * Add bcc'ed recipient to email (with optional name).
     *
     * @param string $email
     * @param string|null $name
     * @return static
     */
    public function addBCC(string $email, ?string $name = null): static
    {
        $recipient = new Recipient($email, $name);
        $this->bcc[] = $recipient;

        return $this;
    }

    /**
     * Set from address for email (with optional name).
     *
     * @param string $email
     * @param string|null $name
     * @return static
     */
    public function setFrom(string $email, ?string $name = null): static
    {
        $recipient = new Recipient($email, $name);
        $this->from = $recipient;

        return $this;
    }

    /**
     * Set email subject.
     *
     * @param string $subject
     * @return static
     */
    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set text body for email.
     *
     * @param string $body
     * @return static
     */
    public function setTextBody(string $body): static
    {
        $this->plainTextBody = $body;

        return $this;
    }

    /**
     * Set HTML body for email.
     *
     * @param string $body
     * @return static
     */
    public function setHtmlBody(string $body): static
    {
        $this->htmlBody = $body;

        return $this;
    }

    /**
     * Add attachment to email.
     *
     * @param Attachment $attachment
     * @return static
     */
    public function addAttachment(Attachment $attachment): static
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Add inline attachment to email.
     *
     * @param Attachment $attachment
     * @return static
     */
    public function addInlineAttachment(Attachment $attachment): static
    {
        $this->inlineAttachments[] = $attachment;

        return $this;
    }

    /**
     * Set ReplyTo address for email (with optional name).
     *
     * @param string $email
     * @param string|null $name
     * @return static
     */
    public function setReplyTo(string $email, ?string $name = null): static
    {
        $this->replyTo = new Recipient($email, $name);

        return $this;
    }

    private function buildToBlock(): string
    {
        return 'To: ' . $this->buildAddressBlock($this->to);
    }

    private function buildFromBlock(): string
    {
        /** @var array<Recipient> $from */
        $from = [$this->from];

        return 'From: ' . $this->buildAddressBlock($from);
    }

    private function buildReplyToBlock(): string
    {
        if ($this->replyTo == null) {
            return '';
        }

        return 'Reply-To: ' . $this->buildAddressBlock([$this->replyTo]);
    }

    private function buildCcBlock(): string
    {
        if (count($this->cc) == 0) {
            return '';
        }

        return 'Cc: ' . $this->buildAddressBlock($this->cc);
    }

    private function buildBccBlock(): string
    {
        if (count($this->bcc) == 0) {
            return '';
        }

        return 'Bcc: ' . $this->buildAddressBlock($this->bcc);
    }

    /**
     * @param array<Recipient> $addresses
     * @return string
     */
    private function buildAddressBlock(array $addresses): string
    {
        $return = '';
        foreach ($addresses as $to) {
            $return .= $to->getFormattedAddress() . ",";
        }

        return substr($return, 0, -1) . "\r\n";
    }

    private function buildBody(): string
    {
        $return = 'Content-Type: multipart/alternative; boundary="ALT' . $this->separator . '"' . "\r\n\r\n";
        if ($this->plainTextBody) {
            $return .= '--ALT' . $this->separator . "\r\n";
            $return .= 'Content-type: text/plain' . "\r\n";
            $return .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";

            $return .= $this->plainTextBody . "\r\n\r\n";
        }
        if ($this->htmlBody) {
            $return .= '--ALT' . $this->separator . "\r\n";
            $return .= 'Content-type: text/html' . "\r\n";
            $return .= 'Content-Transfer-Encoding: 7bit' . "\r\n\r\n";
            $return .= ($this->htmlBody) . "\r\n\r\n";
        }
        $return .= '--ALT' . $this->separator . '--' . "\r\n\r\n";
        $return .= $this->buildInlineAttachments();
        $return .= '--REL' . $this->separator . '--' . "\r\n\r\n";

        return $return;
    }

    private function buildAttachments(): string
    {
        $return = '';

        /** @var array<Attachment> $attachments */
        $attachments = $this->attachments;
        if (count($attachments) != 0) {
            foreach ($attachments as $attachment) {
                $return .= '--MIXED' . $this->separator;
                $return .= $attachment->getAttachmentString();
            }
        }

        return $return;
    }

    private function buildInlineAttachments(): string
    {
        $return = '';

        /** @var array<Attachment> $attachments */
        $attachments = $this->inlineAttachments;
        if (count($attachments) != 0) {
            foreach ($attachments as $attachment) {
                $return .= '--REL' . $this->separator;
                $return .= $attachment->getAttachmentString();
            }
        }

        return $return;
    }

    public function buildEmail(): string
    {
        $email = $this->buildToBlock();
        $email .= $this->buildCcBlock();
        $email .= $this->buildBccBlock();
        $email .= $this->buildFromBlock();
        $email .= $this->buildReplyToBlock();
        $email .= 'Subject: ' . $this->subject . "\r\n";
        $email .= 'MIME-version: 1.0' . "\r\n";
        $email .= 'Content-Type: multipart/mixed; boundary="MIXED' . $this->separator . '"' . "\r\n\r\n";
        $email .= 'If you are reading this, please use a MIME compatible e-mail client.' . "\r\n\r\n";
        $email .= '--MIXED' . $this->separator . "\r\n";
        $email .= 'Content-Type: multipart/related; boundary="REL' . $this->separator . '"' . "\r\n\r\n";
        $email .= '--REL' . $this->separator . "\r\n";
        $email .= $this->buildBody();
        $email .= $this->buildAttachments();
        $email .= '--MIXED' . $this->separator . '--';

        return $email;
    }

    /**
     * Send email - currently uses sendmail only.
     *
     * @throws InvalidArgumentException
     * @throws ServerFailureException
     * @todo Add smtp functionality.
     *
     */
    public function sendEmail(): void
    {
        if ($this->from === null) {
            throw new InvalidArgumentException('No from address specified');
        }
        if ($this->sendmailPath) {
            $descriptorSpec = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
            $pipes = [];
            $proc = proc_open(
                $this->sendmailPath . $this->from->getEmail(),
                $descriptorSpec,
                $pipes
            ) or throw new ServerFailureException('Could not open email process');
            fwrite($pipes[0], $this->buildEmail());
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($proc);
        }
    }
}
