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

namespace Form\Field;

use Feast\Exception\InvalidArgumentException;
use Feast\Form\Field;
use Feast\Form\Field\Select;
use Feast\Form\Label;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{

    public function testCreateNoValues(): void
    {
        $select = new Select(
            'test',
            'testing',
            'test'
        );
        $this->assertTrue($select instanceof Select);
    }

    public function testSetDefault(): void
    {
        $select = new Select(
            'test',
            'testing',
            'test'
        );
        $this->expectException(InvalidArgumentException::class);
        $select->setDefault('test');
    }

    public function testAddValueNoValue(): void
    {
        $radio = new Select(
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
        $radio = new Select(
            'test', 'testing', 'test'
        );

        $this->expectException(InvalidArgumentException::class);
        $radio->addValue(
            '',
            new Field\Value('true')
        );
    }

    public function testToString(): void
    {
        $select = new Select(
            'test',
            'testing',
        );
        $select->setLabel(new Label('testing'));
        $select->setId();
        $select->addMeta('test', 'test');
        $select->addValue('true', new Field\SelectValue('true', 'test'));

        $label = new Label();
        $label->setClass('test')
            ->setId('label')
            ->setStyle('color:#fff')
            ->setText('test')
            ->setFor('test')
            ->setAttributes('testa="testb"')
            ->setPosition(
                Label::LABEL_POSITION_FIRST
            );
        $select->setLabel($label);
        $this->assertEquals(
            '<label for="test" class="test" id="label" style="color:#fff" testa="testb">test</label><select id="testing_test" name="test" style="" class=""><option value="true">test</option>' . "\n" . '</select>',
            $select->toString()
        );
    }

    public function testToStringLabelLast(): void
    {
        $select = new Select(
            'test',
            'testing',
        );
        $select->setLabel(new Label('testing'));
        $select->addMeta('test', 'test');
        $select->addValue('true', new Field\SelectValue('true', 'test'));
        $select->setId();

        $label = new Label();
        $label->setClass('test')
            ->setId('label')
            ->setStyle('color:#fff')
            ->setText('test')
            ->setPosition(
                Label::LABEL_POSITION_LAST
            );
        $select->setLabel($label);
        $this->assertEquals(
            '<select id="testing_test" name="test" style="" class=""><option value="true">test</option>' . "\n" . '</select><label class="test" id="label" style="color:#fff">test</label>',
            $select->toString()
        );
    }

    public function testSetValue(): void
    {
        $select = new Select(
            'test',
            'testing'

        );
        $select->setId();

        $select->setLabel(new Label('testing'));
        $select->addValue('true', new Field\SelectValue('true', 'test'));
        $select->addValue('false', new Field\SelectValue('false', 'ing'));
        $select->setValue('false');
        $this->assertEquals(
            '<label>testing</label><select id="testing_test" name="test" style="" class=""><option value="true">test</option>' . "\n" . '<option value="false" selected="selected">ing</option>' . "\n" . '</select>',
            $select->toString()
        );
    }

    public function testClearSelected(): void
    {
        $select = new Select(
            'test',
            'testing'

        );
        $select->setId();

        $select->setLabel(new Label('testing'));
        $select->addValue('true', new Field\SelectValue('true', 'test'));
        $select->addValue('false', new Field\SelectValue('false', 'ing'));
        $select->setValue('false');
        $select->clearSelected();
        $this->assertEquals(
            '<label>testing</label><select id="testing_test" name="test" style="" class=""><option value="true">test</option>' . "\n" . '<option value="false">ing</option>' . "\n" . '</select>',
            $select->toString()
        );
    }

    public function testGetValuesForValidation(): void
    {
        $select = new Select(
            'test',
            'testing'

        );
        $select->setId();

        $select->setLabel(new Label('testing'));
        $select->addValue('true', new Field\SelectValue('true', 'test'));
        $select->addValue('false', new Field\SelectValue('false', 'ing'));
        $select->setValue('false');
        $values = $select->getValuesForValidation();
        $this->assertEquals(
            ['false'],$values
        );
    }
}
