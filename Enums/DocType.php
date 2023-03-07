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

enum DocType: string
{
    case HTML_4_01_FRAMESET = 'html401frame';
    case HTML_4_01_STRICT = 'html401strict';
    case HTML_4_01_TRANSITIONAL = 'html401transitional';
    case HTML_5 = 'html5';
    case XHTML_1_0_FRAMESET = 'xhtml1frame';
    case XHTML_1_0_STRICT = 'xhtml1strict';
    case XHTML_1_0_TRANSITIONAL = 'xhtml1transitional';
    case XHTML_1_1 = 'xhtml11';
}
