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

namespace Form\Field;

use Feast\Exception\InvalidArgumentException;
use Feast\Form\Field;
use Feast\Form\Field\Radio;
use Feast\Form\Label;
use PHPUnit\Framework\TestCase;

class RadioTest extends TestCase
{

    public function testClearSelected(): void
    {
        $radio = new Radio(
            'test', 'testing'
        );
        $label = new Label('test');
        $radio->addValue('true', new Field\Value('true', $label, false));

        $radio->setValue('true');
        $output = $radio->toString(true, 'true');
        $this->assertEquals(
            '<label for="testing_test_true">test</label><input id="testing_test_true" class="" type="radio" name="test" value="true" checked="checked" />',
            $output
        );
        $radio->clearSelected();
        $output = $radio->toString(true, 'true');
        $this->assertEquals(
            '<label for="testing_test_true">test</label><input id="testing_test_true" class="" type="radio" name="test" value="true" />',
            $output
        );
        $this->assertInstanceOf(Radio::class,$radio);
    }

    public function testSetValue(): void
    {
        $radio = new Radio(
            'test', 'testing'
        );
        $label = new Label('test');
        $radio->addValue('true', new Field\Value('true', $label, false));

        $radio->setValue('true');
        $output = $radio->toString(true, 'true');
        $this->assertEquals(
            '<label for="testing_test_true">test</label><input id="testing_test_true" class="" type="radio" name="test" value="true" checked="checked" />',            $output
        );
    }

    public function testToStringException(): void
    {
        $radio = new Radio(
            'test', 'testing'
        );
        $this->expectException(\Exception::class);
        $radio->toString(true, 'tests');
    }

    public function testToStringValidLabelFirst(): void
    {
        $radio = new Radio(
            'test', 'testing'
        );
        $radio->addValue(
            'true',
            new Field\Value(
                'true',
                new Label(
                    'test',
                    null,
                    'label',
                    'labeltrue',
                    'color: red;',
                    'onclick="test"'
                ),
                false,
                attributes: 'test="test"'
            )
        );
        $result = $radio->toString(true, 'true');
        $this->assertEquals(
            '<label for="testing_test_true" class="label" id="labeltrue" style="color: red;" onclick="test">test</label><input id="testing_test_true" class="" type="radio" name="test" value="true" test="test" />',
            $result
        );
    }

    public function testToStringValidLabelLast(): void
    {
        $radio = new Radio(
            'test', 'testing'
        );
        $radio->addValue(
            'true',
            new Field\Value(
                'true',
                new Label(
                    'test',
                    null,
                    'label',
                    'labeltrue',
                    'color: red;',
                    'onclick="test"',
                    Label::LABEL_POSITION_LAST
                ),
                false,
                attributes: 'test="test"'
            )
        );
        $result = $radio->toString(true, 'true');
        $this->assertEquals(
            '<input id="testing_test_true" class="" type="radio" name="test" value="true" test="test" /><label for="testing_test_true" class="label" id="labeltrue" style="color: red;" onclick="test">test</label>',
            $result
        );
    }

    public function testAddValueNoValue(): void
    {
        $radio = new Radio(
            'test', 'testing', 'test'
        );

        $this->expectException(InvalidArgumentException::class);
        $radio->addValue(
            '',
            new Field\Value('true', new Label())
        );
    }

    public function testAddValueNoLabel(): void
    {
        $radio = new Radio(
            'test', 'testing'
        );
        $radio->addValue(
            'true',
            new Field\Value(
                'true',
                new Label(
                    'test',
                    null,
                    'label',
                    'labeltrue',
                    'color: red;',
                    'onclick="test"',
                    Label::LABEL_POSITION_LAST
                ),
                false,
                attributes: 'test="test"'
            )
        );
        $result = $radio->toString(false, 'true');
        $this->assertEquals(
            '<input id="testing_test_true" class="" type="radio" name="test" value="true" test="test" />',
            $result
        );
    }

}
