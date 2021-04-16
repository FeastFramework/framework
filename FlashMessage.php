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

use Feast\Session\Session;

/**
 * Manages Flash Messages
 * Flash messages are single read messages that are then wiped from the session.
 */
class FlashMessage
{
    /**
     * Set a flash message.
     * 
     * @param string $name
     * @param string $value
     * @throws ServiceContainer\NotFoundException
     */
    public static function setMessage(string $name, string $value): void
    {
        $flash = di(Session::class)->getNamespace('FlashMessage');
        $flash->$name = $value;
    }

    /**
     * Get and erase a flash message.
     * 
     * @param string $name
     * @return string|null
     * @throws ServiceContainer\NotFoundException
     */
    public static function getMessage(string $name): ?string
    {
        $flash = di(Session::class)->getNamespace('FlashMessage');
        if (!empty($flash->$name)) {
            $flashMessage = (string)$flash->$name;
            unset($flash->$name);
            return $flashMessage;
        }

        return null;
    }
}
