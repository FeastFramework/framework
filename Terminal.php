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

    /**
     * Prompt for user input on the terminal.
     *
     * @param string $question
     * @param string|false $default
     * @return false|string
     */
    public function getInputFromPrompt(string $question, string|false $default = false): false|string
    {
        $prompt = $question;

        if ($default !== false) {
            $prompt .= '[' . $default . ']';
        }
        $value = readline($prompt);
        if ( $value === '' || $value === false ) {
            return $default;
        }
        readline_add_history($value);
        return $value;
    }

    /**
     * Prompt for user input on the terminal. Return when sentinelValue is entered by user.
     *
     * @param string $question
     * @param string $sentinelValue
     * @param bool $allowBlank
     * @return array
     */
    public function getArrayFromPromptWithSentinel(string $question, string $sentinelValue, bool $allowBlank = false): array
    {
        $return = [];
        $this->message($question);
        $this->message('Enter \'' . $sentinelValue . '\' when finished.');
        do {
            $value = $this->getInputFromPrompt('[]');
            if ($this->isValueValid($value,$allowBlank,$sentinelValue)) {
                $return[] = $value;
            }
        } while ($value !== $sentinelValue);

        return $return;
    }
    
    /**
     * Prompt for user input on the terminal for `count` results.
     *
     * This will return an array of answers. Will return when count is reached
     *
     * @param string $question
     * @param int $count
     * @param string|null $subsequentQuestion If passed in, after the first question, the prompt will change to this value.
     * @param bool $allowBlank
     * @return array
     */
    public function getArrayFromPromptWithCount(
        string $question,
        int $count,
        ?string $subsequentQuestion = null,
        bool $allowBlank = false
    ): array {
        $return = [];
        for ($i = 0; $i < $count; $i++) {
            $value = $this->getInputFromPrompt($question);
            if ($this->isValueValid($value,$allowBlank)) {
                $return[] = $value;
            } else {
                $i--;
            }
            
            if ( $subsequentQuestion !== null) {
                $question = $subsequentQuestion;
                $subsequentQuestion = null;
            }
        }
        return $return;
    }

    protected function isValueValid(string|false $value, bool $allowBlank, string|null $sentinelValue = null): bool
    {
        if ( $value === false ) {
            $value = '';
        }
        if ( $sentinelValue !== null && $value === $sentinelValue ) {
            return false;
        }
        if ( $value === '' && $allowBlank === false ) {
            return false;
        }
        return true;
    }
}
