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

namespace Feast\Exception;

use Exception;

use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\RequestInterface;
use Feast\Interfaces\ResponseInterface;
use Feast\Main;
use Throwable;

use function di;

use const RUN_AS;

/**
 * Server failure exception. Allows instantly overriding http status code. Always caught,
 * returning the error code if present, or the error message if not, to the client if display errors is true.
 */
class ServerFailureException extends Exception
{

    protected string $runAs = RUN_AS;

    /**
     * Construct a new Exception, and include the response code.
     *
     * @param string $message
     * @param int|null $responseCode
     * @param int $errorCode
     * @param Throwable|null $previousException
     * @param string|null $overrideRunAs
     */
    public function __construct(
        string $message,
        private ?int $responseCode = null,
        int $errorCode = 0,
        Throwable $previousException = null,
        ?string $overrideRunAs = null
    ) {
        parent::__construct($message, 0, $previousException);
        $this->code = $errorCode;
        if ($overrideRunAs !== null) {
            $this->runAs = $overrideRunAs;
        }
    }

    /**
     * Get the response code
     *
     * @return int|null
     */
    public function getResponseCode(): ?int
    {
        return $this->responseCode;
    }

    /**
     * Print the error either as a simple error or as a detailed error if errors enabled.
     *
     * @throws \Feast\ServiceContainer\NotFoundException
     * @throws Exception
     */
    public function printError(): void
    {
        $response = di(ResponseInterface::class);
        $config = di(ConfigInterface::class);
        $responseCode = $this->getResponseCode();
        if ($responseCode !== null) {
            $response->setResponseCode($responseCode);
            $response->sendResponseCode();
        }
        $request = di(RequestInterface::class);
        $format = $request->getArgumentString('format');
        if ($format == 'json') {
            $this->echoJsonError();
            return;
        }
        if ($this->runAs === Main::RUN_AS_WEBAPP && $config->getSetting('showerrors')) {
            $this->printHtmlException($this);
        } elseif ($this->runAs === Main::RUN_AS_CLI) {
            $this->printNonHtmlException($this);
        }
    }

    /**
     * Print parent exception, if any.
     *
     * @throws \Feast\ServiceContainer\NotFoundException
     */
    public function printParentException(): void
    {
        $config = di(ConfigInterface::class);
        if ($this->getPrevious() === null) {
            return;
        }
        if ($this->runAs === Main::RUN_AS_WEBAPP && $config->getSetting('showerrors')) {
            $this->printHtmlException($this->getPrevious());
        } elseif ($this->runAs === Main::RUN_AS_CLI) {
            $this->printNonHtmlException($this->getPrevious());
        }
    }

    protected function echoJsonError(): void
    {
        $error = [
            'error' => [
                'message' => $this->getMessage()
            ]
        ];

        if ($this->getCode()) {
            $error['error']['code'] = $this->getCode();
        }
        echo json_encode($error);
    }

    protected function printHtmlException(Throwable $exception): void
    {
        echo $exception->getCode() ? (string)$exception->getCode() : $exception->getMessage();
        echo '<br />Thrown on line ' . (string)$exception->getLine() . ' in ' . $exception->getFile();
        echo '<table  border="1">';
        $trace = $exception->getTrace();
        echo '<tr><th>File</th><th>Line</th><th>Class</th><th>Function</th></tr>';
        /** @var array<string> $line */
        foreach ($trace as $line) {
            echo '<tr><td>' . $line['file'] . '</td><td>' . $line['line'] . '</td><td>' . $line['class'] . '</td><td>' . $line['function'] . '</td></tr>';
        }
        echo '</table>';
    }

    protected function printNonHtmlException(Throwable $exception): void
    {
        echo $exception->getCode() ? (string)$exception->getCode() . '-' . $exception->getMessage(
            ) : $exception->getMessage();
        echo "\n" . 'Thrown on line ' . (string)$exception->getLine() . ' in ' . $exception->getFile() . "\n\n";
        $trace = $exception->getTraceAsString();
        echo $trace . "\n\n";
    }

}
