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

namespace Feast\Enums;

enum LogLevelCode: int
{
    case DEBUG = 7; //LOG_DEBUG;
    case INFO = 6; // LOG_INFO;
    case NOTICE = 5; //LOG_NOTICE;
    case WARNING = 4; //LOG_WARNING;
    case ERROR = 3; //LOG_ERR;
    case CRITICAL = 2; //LOG_CRIT;
    case ALERT = 1; //LOG_ALERT;
    case EMERGENCY = 0; //LOG_EMERG;

}
