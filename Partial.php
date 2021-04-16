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

/**
 * Class to render and output a partial
 */
class Partial extends View
{

    /**
     * Assemble and output a partial view.
     *
     * @param string $file - filename to use for the view (in Views folder).
     * @param View $view - View object
     * @param mixed $variables - variables to be assigned onto the view.
     * @param mixed $loopId - optional identifier that can be used in the template.
     * @param bool $includeView - if true, all parameters from the View object are copied.
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        string $file,
        View $view,
        mixed $variables,
        private mixed $loopId = null,
        bool $includeView = true
    ) {
        if ($includeView) {
            /**
             * @var string $key
             * @var string|int|float|bool|object|null|array $val
             */
            foreach (get_object_vars($view) as $key => $val) {
                $this->$key = $val;
            }
        }
        /**
         * @var string $key
         * @var string|int|float|bool|object|null|array $val
         */
        foreach ($variables as $key => $val) {
            $this->$key = $val;
        }

        if (str_ends_with($file, '.phtml')) {
            $file = substr($file, 0, -6);
        }
        /** @psalm-suppress UnresolvableInclude */
        include(APPLICATION_ROOT . 'Views' . DIRECTORY_SEPARATOR . $file . '.phtml');
    }

}
