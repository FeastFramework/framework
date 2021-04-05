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

namespace Feast;

class Terminal
{
    private bool $isTty;

    /**
     * Terminal constructor.
     *
     * @param bool|null $useColor - True to force color, false to disable color, null to let the terminal decide.
     */
    public function __construct(?bool $useColor = null)
    {
        if ($useColor === null) {
            $this->isTty = (function_exists('posix_isatty') ? posix_isatty(STDOUT) : false);
        } else {
            $this->isTty = $useColor;
        }
        echo $this->resetColor();
    }

    /**
     * Get error text.
     * 
     * @param string $text
     * @return string
     */
    public function errorText(string $text): string
    {
        return $this->isTty ? "\e[41;97m" . $text . $this->resetColor() : $text;
    }

    /**
     * Get command text.
     * 
     * @param string $text
     * @return string
     */
    public function commandText(string $text): string
    {
        return $this->isTty ? "\e[33m" . $text . $this->resetColor() : $text;
    }

    /**
     * Get message text.
     * 
     * @param string $text
     * @return string
     */
    public function messageText(string $text): string
    {
        return $this->isTty ? $this->resetColor() . $text : $text;
    }

    /**
     * Reset color codes.
     * 
     * @return string
     */
    private function resetColor(): string
    {
        return $this->isTty ? "\e[0m" : '';
    }

    /**
     * Output an error to the console.
     * 
     * @param string $text
     * @param bool $newLine
     */
    public function error(string $text, bool $newLine = true): void
    {
        echo $this->errorText($text);
        if ($newLine) {
            echo PHP_EOL;
        }
    }

    /**
     * Output command data to the console.
     *
     * @param string $text
     * @param bool $newLine
     */
    public function command(string $text, bool $newLine = true): void
    {
        echo $this->commandText($text);
        if ($newLine) {
            echo PHP_EOL;
        }
    }

    /**
     * Output a message to the console.
     *
     * @param string $text
     * @param bool $newLine
     */
    public function message(string $text, bool $newLine = true): void
    {
        echo $this->messageText($text);
        if ($newLine) {
            echo PHP_EOL;
        }
    }

}
