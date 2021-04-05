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

namespace Feast\Form\Validator;

use Feast\Form\Field;

class AlphaNumericSpaces implements Validator
{

    /**
     * Validate a form field as alphanumeric (with spaces allowed).
     *
     * Update the errors array (passed by reference) and
     * returns $valid (passed in as parameter and potentially modified in validator).
     *
     * @param string $key
     * @param string $value
     * @param Field $field
     * @param array $files
     * @param array $errors
     * @param bool $valid
     * @return bool
     */
    public static function validate(
        string $key,
        string $value,
        Field $field,
        array $files,
        array &$errors,
        bool $valid
    ): bool {
        if (!ctype_alnum(str_replace(' ', '', $value))) {
            $errors[] = [$key, 'AlphaNumericSpaces'];
            $valid = false;
        }
        
        return $valid;
    }

}
