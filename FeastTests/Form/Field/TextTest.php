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
use Feast\Exception\ServerFailureException;
use Feast\Form\Field;
use Feast\Form\Field\Text;
use Feast\Form\Filter\Md5;
use Feast\Form\Label;
use Feast\Form\Validator\Alphabetical;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    public function testCreate(): void
    {
        $text = $this->getTextObject();
        $this->assertInstanceOf(Text::class,$text);
    }

    public function testSetValue(): void
    {
        $text = $this->getTextObject();
        $text->setValue('Feast');

        $this->assertTrue($text->value === 'Feast');
    }

    public function testClearSelected(): void
    {
        $text = $this->getTextObject();
        $text->setValue('Feast');
        $text->clearSelected();
        $this->assertTrue($text->value === '');
    }

    public function testToStringLabelFirst(): void
    {
        $text = $this->getTextObject();
        $text->setValue('Feast');

        $this->assertEquals(
            '<label>testing</label><input type="text" name="test" value="Feast" class="testClass secondClass" id="testId" placeholder="enter text" style="color: #fff" required="required" />',
            $text->toString()
        );
    }

    public function testToStringLabelLast(): void
    {
        $text = $this->getTextObject();
        $text->setValue('Feast');
        $text->label->setPosition(Label::LABEL_POSITION_LAST);
        $this->assertEquals(
            '<input type="text" name="test" value="Feast" class="testClass secondClass" id="testId" placeholder="enter text" style="color: #fff" required="required" /><label>testing</label>',
            $text->toString()
        );
    }

    public function testSetId(): void
    {
        $text = $this->getTextObject();
        $text->setValue('Feast');
        $text->setId('realId');
        $this->assertEquals(
            '<input type="text" name="test" value="Feast" class="testClass secondClass" id="realId" placeholder="enter text" style="color: #fff" required="required" />',
            $text->toString(false)
        );
    }

    public function testRemoveClass(): void
    {
        $text = $this->getTextObject();
        $text->setValue('Feast');
        $text->removeClass('secondClass');
        $this->assertEquals(
            '<input type="text" name="test" value="Feast" class="testClass" id="testId" placeholder="enter text" style="color: #fff" required="required" />',
            $text->toString(false)
        );
    }

    public function testSetClass(): void
    {
        $text = $this->getTextObject();
        $text->setValue('Feast');
        $text->setClass('secondClass');
        $this->assertEquals(
            '<input type="text" name="test" value="Feast" class="secondClass" id="testId" placeholder="enter text" style="color: #fff" required="required" />',
            $text->toString(false)
        );
    }

    public function testFilters(): void
    {
        $text = $this->getTextObject();
        $text->addFilter(Md5::class);
        $this->assertEquals([Md5::class], $text->filter);
    }

    public function testAddMeta(): void
    {
        $text = $this->getTextObject();
        $text->addMeta('test', 'testing');
        $this->assertEquals(
            '<input type="text" name="test" class="testClass secondClass" id="testId" placeholder="enter text" style="color: #fff" required="required" test="testing" />',
            $text->toString(false)
        );
    }

    public function testAddMetaDuplicate(): void
    {
        $text = $this->getTextObject();
        $text->addMeta('test', 'testing');
        $text->addMeta('test', 'testing');
        $this->assertEquals(
            '<input type="text" name="test" class="testClass secondClass" id="testId" placeholder="enter text" style="color: #fff" required="required" test="testing" />',
            $text->toString(false)
        );
    }

    public function testSetDefault(): void
    {
        $text = $this->getTextObject();
        $text->setDefault('test');
        $this->assertEquals(
            '<input type="text" name="test" value="test" class="testClass secondClass" id="testId" placeholder="enter text" style="color: #fff" required="required" />',
            $text->toString(false)
        );
    }

    public function testAddValue(): void
    {
        $text = $this->getTextObject();
        $this->expectException(InvalidArgumentException::class);
        $text->addValue('test',new Field\Value('test'));
    }

    public function testAddValidation(): void
    {
        $text = $this->getTextObject();
        $text->addValidator(Alphabetical::class);
        $this->assertEquals([Alphabetical::class], $text->validate);
    }

    public function testFailedCastAsString(): void
    {
        $text = $this->getTextObject();
        $this->expectException(ServerFailureException::class);
        /** @noinspection PhpExpressionResultUnusedInspection */
        (string)$text;
    }

    /**
     * @return Text
     */
    protected function getTextObject(): Text
    {
        $label = new Label('testing');
        $text = new Text(
            'test', 'formTest'
        );
        $text->addClass('testClass')
            ->setLabel($label)->setId('testId')

            ->addClass('secondClass')
            ->setStyle('color: #fff')
            ->setPlaceholder('enter text')
            ->setRequired(true);
        return $text;
    }
}
