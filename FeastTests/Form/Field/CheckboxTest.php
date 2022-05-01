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
use Feast\Form\Field\Checkbox;
use Feast\Form\Label;
use PHPUnit\Framework\TestCase;

class CheckboxTest extends TestCase
{

    public function testClearSelected(): void
    {
        $checkbox = new Checkbox(
            'test', 'testing'
        );
        $label = new Label('test');
        $checkbox->addValue('true', new Field\Value('true', $label, false));

        $checkbox->setValue('true');
        $output = $checkbox->toString(true, 'true');
        $this->assertEquals(
            '<label for="testing_test_true">test</label><input id="testing_test_true" class="" type="checkbox" name="test" value="true" checked="checked" />',
            $output
        );
        $checkbox->clearSelected();
        $output = $checkbox->toString(true, 'true');
        $this->assertEquals(
            '<label for="testing_test_true">test</label><input id="testing_test_true" class="" type="checkbox" name="test" value="true" />',
            $output
        );
        $this->assertInstanceOf(Checkbox::class,$checkbox);
    }

    public function testSetValue(): void
    {
        $checkbox = new Checkbox(
            'test', 'testing'
        );
        $label = new Label('test');
        $checkbox->addValue('true', new Field\Value('true', $label, false));

        $checkbox->setValue('true');
        $output = $checkbox->toString(true, 'true');
        $this->assertEquals(
            '<label for="testing_test_true">test</label><input id="testing_test_true" class="" type="checkbox" name="test" value="true" checked="checked" />',
            $output
        );
    }

    public function testSetValueOverwrite(): void
    {
        $checkbox = new Checkbox(
            'test', 'testing'
        );
        $label = new Label('test');
        $labelFalse = new Label('test');

        $checkbox->addValue('true', new Field\Value('true', $label, false));
        $checkbox->addValue('false', new Field\Value('false', $labelFalse, false));

        $checkbox->setValue('true');
        $output = $checkbox->toString(true, 'true');
        $this->assertEquals(
            '<label for="testing_test_true">test</label><input id="testing_test_true" class="" type="checkbox" name="test" value="true" checked="checked" />',
            $output
        );
        $output = $checkbox->toString(true, 'false');
        $this->assertEquals(
            '<label for="testing_test_false">test</label><input id="testing_test_false" class="" type="checkbox" name="test" value="false" />',
            $output
        );

        $checkbox->setValue('false');
        $output = $checkbox->toString(true, 'true');
        $this->assertEquals(
            '<label for="testing_test_true">test</label><input id="testing_test_true" class="" type="checkbox" name="test" value="true" />',
            $output
        );
        $output = $checkbox->toString(true, 'false');
        $this->assertEquals(
            '<label for="testing_test_false">test</label><input id="testing_test_false" class="" type="checkbox" name="test" value="false" checked="checked" />',
            $output
        );
    }

    public function testToStringException(): void
    {
        $checkbox = new Checkbox(
            'test', 'testing'
        );
        $this->expectException(\Exception::class);
        $checkbox->toString(true, 'tests');
    }

    public function testToStringValidLabelFirst(): void
    {
        $checkbox = new Checkbox(
            'test', 'testing'
        );
        $checkbox->addValue(
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
        $result = $checkbox->toString(true, 'true');
        $this->assertEquals(
            '<label for="testing_test_true" class="label" id="labeltrue" style="color: red;" onclick="test">test</label><input id="testing_test_true" class="" type="checkbox" name="test" value="true" test="test" />',
            $result
        );
    }

    public function testToStringValidLabelLast(): void
    {
        $checkbox = new Checkbox(
            'test', 'testing'
        );
        $checkbox->addValue(
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
        $result = $checkbox->toString(true, 'true');
        $this->assertEquals(
            '<input id="testing_test_true" class="" type="checkbox" name="test" value="true" test="test" /><label for="testing_test_true" class="label" id="labeltrue" style="color: red;" onclick="test">test</label>',
            $result
        );
    }

    public function testAddValueNoValue(): void
    {
        $checkbox = new Checkbox(
            'test', 'testing', 'test'
        );

        $this->expectException(InvalidArgumentException::class);
        $checkbox->addValue(
            '',
            new Field\Value('true', new Label())
        );
    }

    public function testAddValueNoLabel(): void
    {
        $checkbox = new Checkbox(
            'test', 'testing'
        );
        $checkbox->addValue(
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
        $result = $checkbox->toString(false, 'true');
        $this->assertEquals(
            '<input id="testing_test_true" class="" type="checkbox" name="test" value="true" test="test" />',
            $result
        );
    }

}
