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

use Feast\Exception\NotFoundException;

class Attachment
{
    private ?string $contentId = null;
    private string $content = '';
    private string $contentType = 'application/octet-stream';
    private ?string $fileName = null;

    /**
     * Sets attachment from the contents of a file.
     * 
     * This file will NOT come from PHP's include path.
     *
     * @param string $fileName
     * @return static
     * @throws NotFoundException - If file not found, throws exception.
     */
    public function setContentFromFile(string $fileName): static
    {
        if (is_file($fileName) === false || is_readable($fileName) === false) {
            throw new NotFoundException('Invalid file', 500);
        }
        $file = file_get_contents($fileName);
        $this->setContent($file);

        return $this;
    }

    /**
     * Set attachment content id if attachment is to be used inline.
     *
     * @param string $contentId
     * @return static
     */
    public function setContentId(string $contentId): static
    {
        $this->contentId = '<' . $contentId . '>';

        return $this;
    }

    /**
     * Set the content of attachment from a string.
     *
     * @param string $content
     * @return static
     */
    public function setContent(string $content): static
    {
        $this->content = chunk_split(base64_encode($content));

        return $this;
    }

    /**
     * Set content type of attachment.
     *
     * @param string $type
     * @return static
     */
    public function setContentType(string $type): static
    {
        $this->contentType = $type;

        return $this;
    }

    /**
     * Set filename for attachment if downloadable.
     *
     * @param string $name
     * @return static
     */
    public function setFileName(string $name): static
    {
        $this->fileName = $name;

        return $this;
    }

    /**
     * Get attachment as email string excerpt.
     *
     * @return string
     */
    public function getAttachmentString(): string
    {
        $return = "\r\n" . 'Content-Type: ' . $this->contentType;
        if ($this->fileName != null) {
            $return .= '; name="' . $this->fileName . '"';
        }
        $return .= "\r\n";
        $return .= 'Content-Transfer-Encoding: base64' . "\r\n";
        if ($this->contentId != null) {
            $return .= 'Content-ID: ' . $this->contentId . "\r\n";
            $return .= 'Content-Disposition: inline;' . "\r\n";
        }
        if ($this->fileName != null) {
            $return .= 'Content-Disposition: attachment; filename="' . $this->fileName . '"' . "\r\n";
        }
        $return .= "\r\n" . $this->content . "\r\n\r\n";

        return $return;
    }
}
